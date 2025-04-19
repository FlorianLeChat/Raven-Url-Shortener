<?php

namespace App\Domain\Service;

use DateTime;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\Report;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Repository\ReportRepository;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use const App\LOG_FUNCTION;

/**
 * Service de signalement de liens raccourcis.
 */
final class ReportLinkService
{
	/**
	 * Répertoire des méthodes pour les signalements de liens raccourcis.
	 */
	private readonly ReportRepository $reportRepository;

	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly Link $link,
		private readonly LoggerInterface $logger,
		private readonly ValidatorInterface $validator,
		private readonly EntityManagerInterface $entityManager
	) {
		$this->reportRepository = $this->entityManager->getRepository(Report::class);
	}

	/**
	 * Valide les informations du signalement.
	 */
	private function validateReport(Report $report): void
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$errors = [];
		$violations = $this->validator->validate($report);

		foreach ($violations as $violation) {
			$errors[$violation->getPropertyPath()][] = [
				'code' => $violation->getConstraint()->getErrorName($violation->getCode()),
				'message' => $violation->getMessage()
			];
		}

		if (!empty($errors))
		{
			throw new DataValidationException($errors);
		}
	}

	/**
	 * Création d'un signalement.
	 */
	public function createReport(Request $request): Report
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$email = $request->request->get('email');
		$reason = $request->request->get('reason');

		$report = new Report();
		$report->setLink($this->link);
		$report->setEmail(is_string($email) ? trim($email) : null);
		$report->setReason(is_string($reason) ? trim($reason) : null);
		$report->setCreatedAt(new DateTime());

		$this->validateReport($report);

		$this->reportRepository->save($report, true);

		return $report;
	}
}