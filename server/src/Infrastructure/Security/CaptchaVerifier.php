<?php

namespace App\Infrastructure\Security;

use JsonException;
use DateTimeImmutable;
use AltchaOrg\Altcha\V1\Altcha;
use Symfony\Component\Lock\LockFactory;
use App\Domain\Enum\AccessKeyCapability;
use AltchaOrg\Altcha\V1\ChallengeOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\EventListener\AccessKeyListener;
use App\Infrastructure\Exception\DataValidationException;
use App\Infrastructure\EventListener\AdminApiKeyListener;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Creates the ALTCHA proof of work challenges and checks their solutions, only for a private API:
 *  a public API is meant for scripts and integrations, which the rate limiter protects instead.
 * The browser solves the challenge with `altcha-lib` and sends the base64 payload in the `captcha` body field.
 * A solution is accepted once: the first request acquires a lock on its challenge, never released,
 *  which expires with the challenge. The lock is atomic, so concurrent requests cannot both use it.
 * The lock lives in the database, shared by every server and already needed by the write requests: a store
 *  local to one server, such as files, would let each server accept the same solution once.
 *  When the database cannot be reached, the write requests answer a 503 error.
 *
 * @see https://altcha.org/docs/v2/security-recommendations/#replay-attacks
 */
final readonly class CaptchaVerifier
{
    public const CHALLENGE_LIFETIME = 60;
    public const CHALLENGE_MAX_NUMBER = 100_000;

    private const FIELD_NAME = 'captcha';
    private const LOCK_KEY_PREFIX = 'captcha.';

    private Altcha $altcha;

    /**
     * @param string $hmacKey Secret signing the challenges (`CAPTCHA_HMAC`), never empty for a private API:
     *  the Kernel refuses to boot without it.
     */
    public function __construct(
        private bool $isPrivateApi,
        private string $hmacKey,
        private LockFactory $lockFactory,
        private TranslatorInterface $translator
    ) {
        $this->altcha = new Altcha($this->hmacKey);
    }

    /**
     * Tells whether the write requests of the front-end must carry a solved challenge.
     */
    public function isRequired(): bool
    {
        return $this->isPrivateApi;
    }

    /**
     * Creates a signed challenge expiring after `CHALLENGE_LIFETIME` seconds, with the keys `altcha-lib` reads.
     *
     * @return array{algorithm: string, challenge: string, maxnumber: int, salt: string, signature: string}
     */
    public function createChallenge(): array
    {
        $expires = (new DateTimeImmutable())->setTimestamp(time() + self::CHALLENGE_LIFETIME);
        $challenge = $this->altcha->createChallenge(new ChallengeOptions(
            maxNumber: self::CHALLENGE_MAX_NUMBER,
            expires: $expires
        ));

        return [
            'algorithm' => $challenge->algorithm,
            'challenge' => $challenge->challenge,
            'maxnumber' => $challenge->maxNumber,
            'salt' => $challenge->salt,
            'signature' => $challenge->signature
        ];
    }

    /**
     * Checks the solution sent in the `captcha` field when the API is private, and marks it as used.
     * An administrator request needs none (see AdminApiKeyListener), nor does a request whose access key grants
     *  the `captcha_bypass` capability (see AccessKeyListener).
     * The check runs before the action changes anything, so a refused request leaves no trace.
     * @throws DataValidationException When the solution is missing, invalid, expired or already used.
     * @throws ServiceUnavailableHttpException When the lock store cannot be reached.
     */
    public function verifyRequest(Request $request): void
    {
        $isAdmin = $request->attributes->getBoolean(AdminApiKeyListener::IS_ADMIN);
        $isBypassed = AccessKeyListener::hasCapability($request, AccessKeyCapability::CaptchaBypass);

        if ($isAdmin || $isBypassed || !$this->isRequired()) {
            return;
        }

        $payload = $request->getPayload()->get(self::FIELD_NAME);

        if (!is_string($payload) || $payload === '') {
            $this->throwViolation('CAPTCHA_MISSING_ERROR', 'missing');
        }

        try {
            $data = json_decode((string) base64_decode($payload, true), true, 2, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->throwViolation('CAPTCHA_INVALID_ERROR', 'invalid');
        }

        $challenge = is_array($data) && is_string($data['challenge'] ?? null) ? $data['challenge'] : '';
        $isValidSolution = $this->altcha->verifySolution($payload) && $this->lockChallenge($challenge);

        if (!$isValidSolution) {
            $this->throwViolation('CAPTCHA_INVALID_ERROR', 'invalid');
        }
    }

    /**
     * Acquires the lock marking the challenge as used, and tells whether no previous request held it.
     * @throws ServiceUnavailableHttpException When the lock store cannot be reached.
     */
    private function lockChallenge(string $challenge): bool
    {
        $lockKey = self::LOCK_KEY_PREFIX . hash('sha256', $challenge);

        try {
            return $this->lockFactory->createLock($lockKey, self::CHALLENGE_LIFETIME, false)->acquire();
        } catch (LockAcquiringException $exception) {
            $message = $this->translator->trans('unavailable', domain: 'captcha');

            throw new ServiceUnavailableHttpException(message: $message, previous: $exception);
        }
    }

    /**
     * @throws DataValidationException Always, with one violation on the `captcha` field.
     */
    private function throwViolation(string $code, string $messageKey): never
    {
        throw new DataValidationException([
            self::FIELD_NAME => [
                [
                    'code' => $code,
                    'value' => null,
                    'message' => $this->translator->trans($messageKey, domain: 'captcha')
                ]
            ]
        ]);
    }
}
