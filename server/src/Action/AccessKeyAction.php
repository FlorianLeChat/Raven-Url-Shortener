<?php

namespace App\Action;

use App\Kernel;
use Random\RandomException;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\AccessKey;
use Psr\Cache\InvalidArgumentException;
use App\Domain\Service\AccessKeyService;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Infrastructure\EventListener\OriginListener;
use Symfony\Component\Routing\Requirement\Requirement;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Endpoints letting an administrator create, list and revoke the access keys.
 */
#[Route(
    '/v{version}',
    requirements: ['version' => '1'],
    defaults: [OriginListener::CREDENTIALS_ROUTE => OriginListener::ADMIN_API_KEY],
    stateless: true
)]
final class AccessKeyAction extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AccessKeyService $service
    ) {
    }

    /**
     * Reads a list of strings of the request body. A value that is not a string is kept as an empty string,
     *  so a constraint of AccessKey reports it instead of a server error.
     *
     * @return list<string>
     */
    private function getStringList(Request $request, string $field): array
    {
        $values = $request->getPayload()->all($field);

        return array_values(array_map(
            static fn (mixed $value): string => is_string($value) ? $value : '',
            $values
        ));
    }

    /**
     * Creates a key from the `name`, `capabilities` and optional `allowedOrigins` and `expiration`
     *  of the request body, and answers it with its clear value and a 201 status.
     * @throws RandomException|UniqueConstraintViolationException|ORMException|InvalidArgumentException
     */
    #[Route('/access-key', methods: ['POST'])]
    public function createAccessKey(Request $request): JsonResponse
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $payload = $request->getPayload();
        $accessKey = $this->service->createAccessKey(
            $payload->getString('name'),
            $this->getStringList($request, 'capabilities'),
            $this->getStringList($request, 'allowedOrigins'),
            $payload->getString('expiration')
        );

        return new JsonResponse($accessKey->toArray(), Response::HTTP_CREATED);
    }

    /**
     * Answers every key, without their values.
     */
    #[Route('/access-key', methods: ['GET'])]
    public function listAccessKeys(): JsonResponse
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $accessKeys = $this->service->listAccessKeys();
        $serializedKeys = array_map(static fn (AccessKey $accessKey) => $accessKey->toArray(), $accessKeys);

        return new JsonResponse($serializedKeys, Response::HTTP_OK);
    }

    /**
     * Revokes the key reached by its identifier and answers it.
     * @throws UniqueConstraintViolationException|ORMException|InvalidArgumentException
     */
    #[Route('/access-key/{id}', requirements: ['id' => Requirement::UUID_V7], methods: ['DELETE'])]
    public function revokeAccessKey(AccessKey $accessKey): JsonResponse
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $revokedAccessKey = $this->service->revokeAccessKey($accessKey);

        return new JsonResponse($revokedAccessKey->toArray(), Response::HTTP_OK);
    }
}
