<?php

namespace App\Domain\Service;

use App\Kernel;
use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Factory\LinkFactory;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\Abstract\BaseLinkService;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Reads the details of a shortened link for a visitor.
 */
final class GetLinkDetailsService extends BaseLinkService
{
    /**
     * Compares the `Authorization: Password <password>` header with the hash of the link.
     * @throws UnauthorizedHttpException When the header is missing.
     * @throws AccessDeniedHttpException When the password does not match.
     */
    private function checkPassword(Request $request, Link $link): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $headerPassword = $request->headers->get('Authorization');

        if (empty($headerPassword)) {
            throw new UnauthorizedHttpException('Password', $this->translator->trans('link.password.missing'));
        }

        /** @var string $storedPassword */
        $storedPassword = $link->getPassword();
        $headerPassword = str_replace('Password ', '', $headerPassword);
        $isValidPassword = LinkFactory::verifyPassword($storedPassword, $headerPassword);

        if (!$isValidPassword) {
            throw new AccessDeniedHttpException($this->translator->trans('link.password.invalid'));
        }
    }

    /**
     * Stamps the visit date, which delays the deletion of a link left unvisited for a year.
     * The date is written once a day at most: a day is precise enough for that cleanup,
     *  and a popular link does not cost a database write per visit.
     * @throws UniqueConstraintViolationException|ORMException
     */
    private function saveLinkVisited(Link $link): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $visitedAt = $link->getVisitedAt();
        $isVisitedToday = $visitedAt !== null && $visitedAt >= new DateTimeImmutable('today');

        if ($isVisitedToday) {
            return;
        }

        $link->setVisitedAt(new DateTimeImmutable());

        $this->repository->save($link, true);
    }

    /**
     * @throws GoneHttpException When the expiration date of the link has passed.
     */
    private function checkExpiration(Link $link): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $expiresAt = $link->getExpiresAt();
        $isExpired = $expiresAt !== null && $expiresAt <= new DateTimeImmutable();

        if ($isExpired) {
            throw new GoneHttpException($this->translator->trans('link.expired'));
        }
    }

    /**
     * Records the visit, then answers the link with its previous visit date.
     * An expired link is refused before the password check: it stays unreachable
     *  until the scheduled cleanup deletes it.
     * @throws UniqueConstraintViolationException|ORMException
     */
    public function getLinkDetails(Request $request, Link $link): Link
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->checkExpiration($link);

        $isPasswordProtected = !empty($link->getPassword());

        if ($isPasswordProtected) {
            $this->checkPassword($request, $link);
        }

        $currentDate = $link->getVisitedAt();

        $this->saveLinkVisited($link);
        $link->setVisitedAt($currentDate);

        return $link;
    }
}
