<?php

namespace App\Domain\Service;

use DateTime;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\Report;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Repository\LinkRepository;
use App\Infrastructure\Repository\ReportRepository;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use const App\LOG_FUNCTION;

/**
 * Service de signalement de liens raccourcis.
 */
final class ReportLinkService
{
	/**
	 * Répertoire des méthodes pour les liens raccourcis.
	 */
	private readonly LinkRepository $linkRepository;

	/**
	 * Répertoire des méthodes pour les signalements de liens raccourcis.
	 */
	private readonly ReportRepository $reportRepository;

	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly ValidatorInterface $validator,
		private readonly TranslatorInterface $translator,
		private readonly EntityManagerInterface $entityManager
	) {
		$this->linkRepository = $this->entityManager->getRepository(Link::class);
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
	 * Vérifie si le lien raccourci signalé existe dans la base de données.
	 */
	private function getLinkById(string $id): Link
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$errors = [];
		$result = $this->linkRepository->findOneBy(['id' => $id]);

		if (empty($result))
		{
			$errors['slug'][] = [
				'code' => 'LINK_NOT_FOUND_ERROR',
				'message' => $this->translator->trans('link.not_exist')
			];

			throw new DataValidationException($errors);
		}

		return $result;
	}

	/**
	 * Création d'un signalement.
	 */
	public function createReport(Request $request): Report
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$id = $request->request->get('id');
		$email = $request->request->get('email');
		$reason = $request->request->get('reason');

		$report = new Report();
		$report->setLink(is_string($id) ? $this->getLinkById($id) : null);
		$report->setEmail(is_string($email) ? trim($email) : null);
		$report->setReason(is_string($reason) ? trim($reason) : null);
		$report->setCreatedAt(new DateTime());

		$this->validateReport($report);

		$this->reportRepository->create($report, true);

		return $report;
	}
}