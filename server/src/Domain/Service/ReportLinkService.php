<?php

namespace App\Domain\Service;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\Report;
use App\Domain\Factory\ReportFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Repository\ReportRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Service de signalement de liens raccourcis.
 */
final class ReportLinkService
{
	/**
	 * Nombre maximum de signalements autorisés par lien.
	 */
	private const MAX_REPORTS_PER_LINK = 3;

	/**
	 * Répertoire des méthodes pour les signalements de liens raccourcis.
	 */
	private readonly ReportRepository $repository;

	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly Link $link,
		private readonly LoggerInterface $logger,
		private readonly ValidatorInterface $validator,
		private readonly TranslatorInterface $translator,
		private readonly EntityManagerInterface $entityManager
	) {
		$this->repository = $this->entityManager->getRepository(Report::class);
	}

	/**
	 * Valide les informations du signalement.
	 */
	private function validateReport(Report $report): void
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$errors = [];
		$violations = $this->validator->validate($report);

		foreach ($violations as $violation) {
			$errors[$violation->getPropertyPath()][] = [
				'code' => $violation->getConstraint()?->getErrorName($violation->getCode() ?? '') ?? '',
				'value' => $violation->getInvalidValue(),
				'message' => $violation->getMessage()
			];
		}

		if (!empty($errors))
		{
			throw new DataValidationException($errors);
		}
	}

	/**
	 * Vérifie si un signalement existe déjà pour le lien pour une adresse électronique donnée.
	 */
	protected function checkReportExists(string $email): void
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$result = $this->repository->findOneBy(['link' => $this->link, 'email' => $email]);

		if (!empty($result))
		{
			throw new ConflictHttpException($this->translator->trans('report.duplicated'));
		}
	}

	/**
	 * Vérifie si le lien est considéré comme de confiance.
	 */
	private function checkTrustedLink(Link $link): void
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		if ($link->isTrusted())
		{
			throw new AccessDeniedHttpException($this->translator->trans('report.trusted_link'));
		}
	}

	/**
	 * Vérifie si le nombre maximum de signalements a été atteint pour le lien.
	 */
	private function checkMaximumReports(Link $link): void
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$reports = $this->repository->count(['link' => $link]);

		if ($reports >= self::MAX_REPORTS_PER_LINK)
		{
			throw new TooManyRequestsHttpException(message: $this->translator->trans('report.maximum_reached', [
				'%max%' => self::MAX_REPORTS_PER_LINK
			]));
		}
	}

	/**
	 * Création d'un signalement.
	 */
	public function createReport(Request $request): Report
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$this->checkTrustedLink($this->link);
		$this->checkMaximumReports($this->link);

		$payload = $request->getPayload();

		$email = $payload->getString('email');
		$reason = $payload->getString('reason');

		$report = ReportFactory::create($this->link, $reason, $email);

		$this->validateReport($report);

		if (!empty($report->getEmail()))
		{
			// La vérification doit seulement être effectuée si l'adresse électronique est renseignée.
			$this->checkReportExists($report->getEmail());
		}

		$this->repository->save($report, true);

		return $report;
	}
}