<?php

namespace App\Domain\Service\Abstract;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use App\Domain\Enum\AccessKeyCapability;
use Doctrine\ORM\EntityManagerInterface;
use App\Infrastructure\Security\SecretKey;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Validator\ExpirationDate;
use App\Infrastructure\Repository\LinkRepository;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\EventListener\AccessKeyListener;
use App\Infrastructure\Exception\DataValidationException;
use App\Infrastructure\EventListener\AdminApiKeyListener;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Checks shared by the services creating or changing a link.
 * Each check throws the HTTP exception the API answers with.
 */
abstract class BaseLinkService
{
    /**
     * Options of the request checking a target URL: 5 seconds without data, 10 seconds at most in all.
     */
    private const CHECK_URL_OPTIONS = [
        'timeout' => 5,
        'max_duration' => 10,
        'max_redirects' => 1
    ];

    /**
     * Bytes of the body read to recognize a firewall page (64 KiB).
     */
    private const MAX_BODY_LENGTH = 65536;

    protected readonly LinkRepository $repository;

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly ValidatorInterface $validator,
        protected readonly TranslatorInterface $translator,
        protected readonly HttpClientInterface $httpClient,
        protected readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(Link::class);
    }

    /**
     * Runs the entity constraints on the link.
     * @throws DataValidationException When at least one constraint fails, with every violation by field.
     */
    protected function validateLink(Link $link): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        DataValidationException::throwOnViolations($this->validator->validate($link));
    }

    /**
     * Marks the link as trusted when the request carries an access key granting the `trusted_links` capability
     *  (see AccessKeyListener). LinkFactory computes the flag again from the URL each time it changes,
     *  so every service building or changing a link calls this after it.
     */
    protected function applyAccessKeyTrust(Request $request, Link $link): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        if (AccessKeyListener::hasCapability($request, AccessKeyCapability::TrustedLinks)) {
            $link->setTrusted(true);
        }
    }

    /**
     * Reads the start of the body, then drops the rest of the download.
     * @throws ExceptionInterface
     */
    private function readBodyStart(ResponseInterface $response): string
    {
        $body = '';

        foreach ($this->httpClient->stream($response) as $chunk) {
            $body .= $chunk->getContent();

            if (strlen($body) >= self::MAX_BODY_LENGTH) {
                $response->cancel();
                break;
            }
        }

        return substr($body, 0, self::MAX_BODY_LENGTH);
    }

    /**
     * Tells whether the target site refused the check itself (firewall, rate limiting, bot challenge)
     *  rather than being unreachable: such a URL is accepted.
     * Only the start of the body is searched for the challenge keywords.
     * @throws ExceptionInterface
     */
    private function isBlockedRequest(string $url, ResponseInterface $response): bool
    {
        $content = strtolower($this->readBodyStart($response));
        $keywords = str_contains($content, 'access denied') || str_contains($content, 'verify you are human');
        $isBlocked = in_array($response->getStatusCode(), [403, 429], true) || $keywords;

        if ($isBlocked) {
            $this->logger->warning('Unreachable URL due to firewall or rate limiting: ' . $url);
        }

        return $isBlocked;
    }

    /**
     * Makes sure the target URL answers, following at most one redirection.
     * The body of a 200 response is never downloaded, and only the start of any other one is read.
     * The HTTP client refuses private network addresses outside the test environment (see `config/services.php`).
     * @throws DataValidationException When the URL is unreachable or does not answer with a 200 status.
     */
    protected function checkUrl(string $url): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        try {
            $errors = [];
            $response = $this->httpClient->request('GET', $url, self::CHECK_URL_OPTIONS);

            $isSuccessful = $response->getStatusCode() === 200;
            $isFailed = !$isSuccessful && !$this->isBlockedRequest($url, $response);

            $response->cancel();

            if ($isFailed) {
                $errors['url'][] = [
                    'code' => 'UNREACHABLE_URL_ERROR',
                    'value' => $url,
                    'message' => $this->translator->trans('unreachable_url', domain: 'link')
                ];

                throw new DataValidationException($errors);
            }
        } catch (ExceptionInterface $exception) {
            $errors['url'][] = [
                'code' => 'UNREACHABLE_URL_ERROR',
                'value' => $url,
                'message' => $exception->getMessage()
            ];

            throw new DataValidationException($errors);
        }
    }

    /**
     * @throws DataValidationException When another link already uses the slug.
     */
    protected function checkSlug(string $slug): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $result = $this->repository->findOneBy(['slug' => $slug]);

        if (!empty($result)) {
            $errors = [];
            $errors['slug'][] = [
                'code' => 'DUPLICATE_SLUG_ERROR',
                'value' => $slug,
                'message' => $this->translator->trans('already_used', domain: 'slug')
            ];

            throw new DataValidationException($errors);
        }
    }

    /**
     * Makes sure the expiration date can be parsed before the link is built with it (see ExpirationDate).
     * @throws DataValidationException When DateTimeImmutable does not understand the date.
     */
    protected function checkExpirationFormat(string $expiration): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        ExpirationDate::parse($expiration, $this->translator);
    }

    /**
     * @throws AccessDeniedHttpException When the link has been disabled.
     */
    protected function checkEnabled(Link $link): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        if (!$link->isEnabled()) {
            throw new AccessDeniedHttpException($this->translator->trans('disabled', domain: 'link'));
        }
    }

    /**
     * @throws AccessDeniedHttpException When a report of the link still waits for an administrator.
     */
    protected function checkForReports(Link $link): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        if ($link->hasPendingReports()) {
            throw new AccessDeniedHttpException($this->translator->trans('reported', domain: 'link'));
        }
    }

    /**
     * Compares the hash of the `Authorization: Bearer <key>` header with the stored hash of the API key of the link,
     *  in constant time. A link without an API key refuses every key.
     * An administrator request manages any link without its key (see AdminApiKeyListener).
     * @throws UnauthorizedHttpException When the header is missing.
     * @throws AccessDeniedHttpException When the key is malformed or does not match.
     */
    protected function checkApiKey(Link $link, Request $request): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        if ($request->attributes->getBoolean(AdminApiKeyListener::IS_ADMIN)) {
            return;
        }

        $headerApiKey = $request->headers->get('Authorization');

        if (empty($headerApiKey)) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('missing', domain: 'api_key'));
        }

        $headerApiKey = str_replace('Bearer ', '', $headerApiKey);
        $storedApiKey = $link->getApiKey()?->getKey() ?? '';
        $isMatchingKey = hash_equals($storedApiKey, SecretKey::hash($headerApiKey));
        $isValidApiKey = SecretKey::isWellFormed($headerApiKey) && $isMatchingKey;

        if (!$isValidApiKey) {
            throw new AccessDeniedHttpException($this->translator->trans('invalid', domain: 'api_key'));
        }
    }
}
