<?php

namespace App\Infrastructure\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Reserves the administrator routes, those declaring `OriginListener::ADMIN_API_KEY` as their secret,
 *  to the requests AdminApiKeyListener recognized. No access key opens them, whatever its capabilities.
 * It runs on `kernel.request` after the routing and OriginListener, and before the action resolves its arguments
 *  (see `config/services.php`): a non-administrator cannot tell whether an identifier exists.
 */
final readonly class AdminRouteListener
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    /**
     * Requires the administrator API key in the `Authorization: Bearer <key>` header.
     * @throws UnauthorizedHttpException When the header is missing.
     * @throws AccessDeniedHttpException When the key does not match, or no administrator API key is configured.
     */
    private function denyAccessUnlessAdmin(Request $request): void
    {
        $headerApiKey = $request->headers->get('Authorization');

        if (empty($headerApiKey)) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('missing', domain: 'api_key'));
        }

        if (!$request->attributes->getBoolean(AdminApiKeyListener::IS_ADMIN)) {
            throw new AccessDeniedHttpException($this->translator->trans('invalid', domain: 'api_key'));
        }
    }

    /**
     * Checks the administrator API key on the administrator routes only.
     * @throws UnauthorizedHttpException|AccessDeniedHttpException When the request is not an administrator one.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $routeSecret = $request->attributes->get(OriginListener::CREDENTIALS_ROUTE);
        $isAdminRoute = $routeSecret === OriginListener::ADMIN_API_KEY;

        if ($event->isMainRequest() && $isAdminRoute) {
            $this->denyAccessUnlessAdmin($request);
        }
    }
}
