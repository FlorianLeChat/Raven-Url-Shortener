<?php

namespace App\Domain\Service;

use Exception;
use App\Kernel;
use App\Domain\Entity\Link;
use App\Domain\Factory\LinkFactory;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\Abstract\BaseLinkService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * Updates a shortened link on behalf of its owner.
 */
final class UpdateLinkService extends BaseLinkService
{
    /**
     * Updates the single field named by `field` once the API key is checked.
     * A disabled or reported link cannot be updated.
     * @throws UniqueConstraintViolationException|ORMException|Exception
     */
    public function patchLink(Request $request, Link $link): Link
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->checkApiKey($link, $request);
        $this->checkEnabled($link);
        $this->checkForReports($link);

        $payload = $request->getPayload();
        $field = $payload->getString('field');
        $value = $payload->getString('value');

        if ($field === 'slug') {
            $this->checkSlug($value);
        }

        if ($field === 'expiresAt') {
            $this->checkExpirationFormat($value);
        }

        LinkFactory::patch($link, $field, $value);

        $this->validateLink($link);
        $this->checkUrl($link->getUrl());

        $this->repository->save($link, true);

        return $link;
    }

    /**
     * Replaces the values of the link once the API key is checked, keeping the current value of every field left out.
     * `api-management` set to false deletes the API key: the link can then no longer be managed, for good.
     * A disabled or reported link cannot be updated.
     * @throws UniqueConstraintViolationException|ORMException|Exception
     */
    public function replaceLink(Request $request, Link $link): Link
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->checkApiKey($link, $request);
        $this->checkEnabled($link);
        $this->checkForReports($link);

        $payload = $request->getPayload();

        $url = $payload->getString('url', $link->getUrl());
        $slug = $payload->getString('slug', $link->getSlug());
        $expiration = $payload->getString('expiration', $link->getExpiresAt()?->format('Y-m-d H:i:s') ?? '');
        $apiManagement = $payload->getBoolean('api-management', true);

        if ($link->getSlug() !== $slug) {
            $this->checkSlug($slug);
        }

        $this->checkExpirationFormat($expiration);

        $options = [
            'url' => $url,
            'slug' => $slug,
            'expiration' => $expiration
        ];

        if ($payload->has('password')) {
            $options['password'] = $payload->getString('password');
        }

        LinkFactory::update($link, $options);

        $this->validateLink($link);
        $this->checkUrl($link->getUrl());

        $apiKey = $link->getApiKey();

        if (!$apiManagement && $apiKey !== null) {
            $this->entityManager->remove($apiKey);
            $link->setApiKey(null);
        }

        $this->repository->save($link, true);

        return $link;
    }
}
