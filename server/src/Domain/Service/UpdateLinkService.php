<?php

namespace App\Domain\Service;

use Exception;
use App\Kernel;
use App\Domain\Entity\Link;
use Random\RandomException;
use Psr\Log\LoggerInterface;
use App\Domain\Factory\LinkFactory;
use App\Domain\Factory\ApiKeyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\Abstract\BaseLinkService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * Updates a shortened link on behalf of its owner.
 */
final class UpdateLinkService extends BaseLinkService
{
    /**
     * @param Link $link Link reached by the request, resolved from its identifier or its slug.
     */
    public function __construct(
        protected Link $link,
        LoggerInterface $logger,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        HttpClientInterface $httpClient,
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($logger, $validator, $translator, $httpClient, $entityManager);
    }

    /**
     * Updates the single field named by `field` once the API key is checked.
     * A disabled or reported link cannot be updated.
     * @throws UniqueConstraintViolationException|ORMException|Exception
     */
    public function patchLink(Request $request): Link
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->checkApiKey($this->link, $request);
        $this->checkEnabled($this->link);
        $this->checkForReports($this->link);

        $payload = $request->getPayload();
        $field = $payload->getString('field');
        $value = $payload->getString('value');

        if ($field === 'slug') {
            $this->checkSlug($value);
        }

        $this->link = LinkFactory::patch($this->link, $field, $value);

        $this->validateLink($this->link);
        $this->checkUrl($this->link->getUrl());

        $this->repository->save($this->link, true);

        return $this->link;
    }

    /**
     * Replaces the values of the link once the API key is checked, keeping the current value of every field left out.
     * A disabled or reported link cannot be updated.
     * @throws UniqueConstraintViolationException|ORMException|RandomException|Exception
     */
    public function replaceLink(Request $request): Link
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->checkApiKey($this->link, $request);
        $this->checkEnabled($this->link);
        $this->checkForReports($this->link);

        $payload = $request->getPayload();

        $url = $payload->getString('url', $this->link->getUrl());
        $slug = $payload->getString('slug', $this->link->getSlug());
        $password = $payload->getString('password', $this->link->getPassword() ?? '');
        $expiration = $payload->getString('expiration', $this->link->getExpiresAt()?->format('Y-m-d H:i:s') ?? '');
        $apiManagement = $payload->getBoolean('api-management', $this->link->getApiKey() !== null);

        if ($apiManagement && $this->link->getApiKey() === null) {
            // Unfinished feature, kept on purpose: checkApiKey() still refuses every link
            //  without an API key, so no test can reach this block.
            // @codeCoverageIgnoreStart
            $apiKey = ApiKeyFactory::create($this->link);
            $this->link->setApiKey($apiKey);
            // @codeCoverageIgnoreEnd
        }

        if ($this->link->getSlug() !== $slug) {
            $this->checkSlug($slug);
        }

        $this->link = LinkFactory::update($this->link, [
            'url' => $url,
            'slug' => $slug,
            'password' => $password,
            'expiration' => $expiration
        ]);

        $this->validateLink($this->link);
        $this->checkUrl($this->link->getUrl());

        $this->repository->save($this->link, true);

        return $this->link;
    }
}
