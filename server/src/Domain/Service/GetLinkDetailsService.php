<?php

namespace App\Domain\Service;

use App\Kernel;
use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Factory\LinkFactory;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\Abstract\BaseLinkService;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Service de récupération des informations d'un lien raccourci.
 */
final class GetLinkDetailsService extends BaseLinkService
{
	/**
	 * Vérification du mot de passe pour accéder au lien.
	 */
	private function checkPassword(Request $request, Link $link): void
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$headerPassword = $request->headers->get('Authorization');

		if (empty($headerPassword))
		{
			throw new UnauthorizedHttpException('Basic', $this->translator->trans('link.password.missing'));
		}

		$headerPassword = str_replace('Basic ', '', $headerPassword);
		$headerPassword = base64_decode($headerPassword, true);

		if (empty($headerPassword))
		{
			throw new AccessDeniedHttpException($this->translator->trans('link.password.invalid'));
		}

		/** @var string $storedPassword */
		$storedPassword = $link->getPassword();
		$isValidPassword = LinkFactory::verifyPassword($storedPassword, $headerPassword);

		if (!$isValidPassword)
		{
			throw new AccessDeniedHttpException($this->translator->trans('link.password.invalid'));
		}
	}

	/**
	 * Enregistrement de la visite d'un lien raccourci.
	 */
	private function saveLinkVisited(Link $link): void
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$link->setVisitedAt(new DateTimeImmutable());

		$this->repository->save($link, true);
	}

	/**
	 * Récupération des informations d'un lien raccourci.
	 */
	public function getLinkDetails(Request $request, Link $link): Link
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$isPasswordProtected = !empty($link->getPassword());

		if ($isPasswordProtected)
		{
			$this->checkPassword($request, $link);
		}

		$currentDate = $link->getVisitedAt();

		$this->saveLinkVisited($link);
		$link->setVisitedAt($currentDate);

		return $link;
	}
}