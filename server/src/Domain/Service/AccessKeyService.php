<?php

namespace App\Domain\Service;

use App\Kernel;
use Random\RandomException;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\AccessKey;
use Psr\Cache\InvalidArgumentException;
use App\Domain\Factory\AccessKeyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use App\Infrastructure\Security\AccessKeyCache;
use App\Infrastructure\Validator\ExpirationDate;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Creates, lists and revokes the access keys on behalf of an administrator (see AdminRouteListener).
 */
final readonly class AccessKeyService
{
    public function __construct(
        private LoggerInterface $logger,
        private AccessKeyCache $accessKeyCache,
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Creates a key, for the API (see AccessKeyAction) or the console (see AccessKeyCommand).
     * The returned key holds its clear value, never disclosed again.
     * The cached origins are refreshed, so a browser can call from the new origins at once.
     *
     * @param list<string> $capabilities
     * @param list<string> $allowedOrigins
     * @param string $expiration Any date understood by PHP, empty when the key never expires.
     * @throws DataValidationException When a value breaks a constraint of AccessKey, or the date cannot be parsed.
     * @throws RandomException|UniqueConstraintViolationException|ORMException|InvalidArgumentException
     */
    public function createAccessKey(
        string $name,
        array $capabilities,
        array $allowedOrigins,
        string $expiration
    ): AccessKey {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $expiresAt = ExpirationDate::parse($expiration, $this->translator);

        $accessKey = AccessKeyFactory::create($name, $capabilities, $allowedOrigins, $expiresAt);

        DataValidationException::throwOnViolations($this->validator->validate($accessKey));

        $this->entityManager->persist($accessKey);
        $this->entityManager->flush();
        $this->accessKeyCache->forget($accessKey);

        return $accessKey;
    }

    /**
     * Lists every key, the most recent first, revoked and expired ones included.
     *
     * @return AccessKey[]
     */
    public function listAccessKeys(): array
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        return $this->entityManager->getRepository(AccessKey::class)->findBy([], ['createdAt' => 'DESC']);
    }

    /**
     * Revokes the key: the requests sending it are refused from then on, its cached entry included,
     *  and it stays listed.
     * @throws ConflictHttpException When the key has already been revoked.
     * @throws UniqueConstraintViolationException|ORMException|InvalidArgumentException
     */
    public function revokeAccessKey(AccessKey $accessKey): AccessKey
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        if ($accessKey->getRevokedAt() !== null) {
            throw new ConflictHttpException($this->translator->trans('already_revoked', domain: 'access_key'));
        }

        AccessKeyFactory::revoke($accessKey);

        $this->entityManager->flush();
        $this->accessKeyCache->forget($accessKey);

        return $accessKey;
    }
}
