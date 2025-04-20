<?php

namespace App\Domain\Service;

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

use const App\LOG_FUNCTION;

/**
 * Service de signalement de liens raccourcis.
 */
final class ReportLinkService
{
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
	 * Vérifie si un signalement existe déjà pour le lien pour une adresse électronique donnée.
	 */
	protected function checkReportExists(string $email): void
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$result = $this->repository->findOneBy(['link' => $this->link, 'email' => $email]);

		if (!empty($result))
		{
			throw new ConflictHttpException($this->translator->trans('report.duplicated'));
		}
	}

	/**
	 * Création d'un signalement.
	 */
	public function createReport(Request $request): Report
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$email = $request->request->get('email');
		$reason = $request->request->getString('reason');

		$report = ReportFactory::create($this->link, $reason, $email);

		$this->validateReport($report);

		if (!empty($email))
		{
			// La vérification doit seulement être effectuée si l'adresse électronique est renseignée.
			$this->checkReportExists($report->getEmail());
		}

		$this->repository->save($report, true);

		return $report;
	}
}