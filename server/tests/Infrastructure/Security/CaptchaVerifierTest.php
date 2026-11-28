<?php

namespace App\Tests\Infrastructure\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use AltchaOrg\Altcha\V1\Altcha;
use Symfony\Component\Lock\LockFactory;
use AltchaOrg\Altcha\V1\Hasher\Algorithm;
use AltchaOrg\Altcha\V1\ChallengeOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Lock\Store\InMemoryStore;
use App\Infrastructure\Security\CaptchaVerifier;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\Exception\DataValidationException;
use App\Infrastructure\EventListener\AdminApiKeyListener;
use Symfony\Component\Lock\Exception\LockStorageException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Tests of CaptchaVerifier, for a public and a private API.
 */
final class CaptchaVerifierTest extends TestCase
{
    private const HMAC_KEY = 'captcha-hmac-key';

    private function createVerifier(
        bool $isPrivateApi,
        string $hmacKey = self::HMAC_KEY,
        PersistingStoreInterface $lockStore = new InMemoryStore()
    ): CaptchaVerifier {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        return new CaptchaVerifier($isPrivateApi, $hmacKey, new LockFactory($lockStore), $translator);
    }

    /**
     * Solves a challenge as `altcha-lib` does in the browser and encodes the payload sent in the `captcha` field.
     *
     * @param array{algorithm: string, challenge: string, maxnumber: int, salt: string, signature: string} $challenge
     */
    private function solve(array $challenge): string
    {
        $altcha = new Altcha(self::HMAC_KEY);
        $algorithm = Algorithm::from($challenge['algorithm']);
        $solution = $altcha->solveChallenge(
            $challenge['challenge'],
            $challenge['salt'],
            $algorithm,
            $challenge['maxnumber']
        );

        return base64_encode(json_encode([
            'algorithm' => $challenge['algorithm'],
            'challenge' => $challenge['challenge'],
            'number' => $solution?->number,
            'salt' => $challenge['salt'],
            'signature' => $challenge['signature']
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * Runs the check on a request carrying the payload and returns the code of the violation, if any.
     */
    private function getViolationCode(CaptchaVerifier $verifier, ?string $payload): ?string
    {
        $body = $payload !== null ? ['captcha' => $payload] : [];

        try {
            $verifier->verifyRequest(new Request(request: $body));
        } catch (DataValidationException $exception) {
            return $exception->getViolations()['captcha'][0]['code'];
        }

        return null;
    }

    public function testPublicApi(): void
    {
        $verifier = $this->createVerifier(false);

        self::assertFalse($verifier->isRequired());
        self::assertNull($this->getViolationCode($verifier, null));
    }

    public function testPrivateApiWithValidAndReplayedSolution(): void
    {
        $verifier = $this->createVerifier(true);
        $challenge = $verifier->createChallenge();
        $payload = $this->solve($challenge);

        self::assertTrue($verifier->isRequired());
        self::assertSame(CaptchaVerifier::CHALLENGE_MAX_NUMBER, $challenge['maxnumber']);
        self::assertNull($this->getViolationCode($verifier, $payload));
        self::assertSame('CAPTCHA_INVALID_ERROR', $this->getViolationCode($verifier, $payload));
    }

    public function testPrivateApiWithMissingSolution(): void
    {
        $verifier = $this->createVerifier(true);
        $adminRequest = new Request(attributes: [AdminApiKeyListener::IS_ADMIN => true]);

        $verifier->verifyRequest($adminRequest);

        self::assertSame('CAPTCHA_MISSING_ERROR', $this->getViolationCode($verifier, null));
        self::assertSame('CAPTCHA_MISSING_ERROR', $this->getViolationCode($verifier, ''));
    }

    public function testPrivateApiWithInvalidSolution(): void
    {
        $verifier = $this->createVerifier(true);
        $otherKeyVerifier = $this->createVerifier(true, 'another-hmac-key');

        $altcha = new Altcha(self::HMAC_KEY);
        $expiredChallenge = $altcha->createChallenge(new ChallengeOptions(
            maxNumber: 10,
            expires: new DateTimeImmutable('-1 second')
        ));

        $expiredPayload = $this->solve([
            'algorithm' => $expiredChallenge->algorithm,
            'challenge' => $expiredChallenge->challenge,
            'maxnumber' => $expiredChallenge->maxNumber,
            'salt' => $expiredChallenge->salt,
            'signature' => $expiredChallenge->signature
        ]);

        self::assertSame('CAPTCHA_INVALID_ERROR', $this->getViolationCode($verifier, 'not base64'));
        self::assertSame('CAPTCHA_INVALID_ERROR', $this->getViolationCode($verifier, $expiredPayload));
        self::assertSame('CAPTCHA_INVALID_ERROR', $this->getViolationCode(
            $otherKeyVerifier,
            $this->solve($verifier->createChallenge())
        ));
    }

    public function testPrivateApiWithUnavailableLockStore(): void
    {
        $lockStore = $this->createStub(PersistingStoreInterface::class);
        $lockStore->method('save')->willThrowException(new LockStorageException('Connection refused'));
        $verifier = $this->createVerifier(true, lockStore: $lockStore);

        $this->expectException(ServiceUnavailableHttpException::class);
        $this->expectExceptionMessage('unavailable');

        $verifier->verifyRequest(new Request(request: ['captcha' => $this->solve($verifier->createChallenge())]));
    }
}
