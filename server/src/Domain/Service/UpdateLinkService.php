<?php

namespace App\Domain\Service;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use App\Domain\Factory\LinkFactory;
use App\Domain\Factory\ApiKeyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\Abstract\BaseLinkService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateLinkService extends BaseLinkService
{
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

    public function replaceLink(Request $request): Link
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->checkApiKey($this->link, $request);
        $this->checkEnabled($this->link);
        $this->checkForReports($this->link);

        $payload = $request->getPayload();

        $url = $payload->getString('url', $this->link->getUrl());
        $slug = $payload->getString('slug', $this->link->getSlug());
        $password = $payload->getString('password', $this->link->getPassword() ?? '') ?: null;
        $expiration = $payload->getString('expiration', $this->link->getExpiresAt()?->format('Y-m-d H:i:s') ?? '') ?: null;
        $apiManagement = $payload->getBoolean('api-management', $this->link->getApiKey() !== null);

        if ($apiManagement && $this->link->getApiKey() === null) {
            $apiKey = ApiKeyFactory::create($this->link);
            $this->link->setApiKey($apiKey);
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
