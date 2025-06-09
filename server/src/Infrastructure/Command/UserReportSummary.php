<?php

namespace App\Infrastructure\Command;

use Exception;
use App\Domain\Entity\Report;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Commande pour la génération d'un résumé des signalements d'utilisateurs.
 */
#[AsCommand('app:reports-summary', 'Creates a summary of user reports and sends it by email.')]
final class UserReportSummary extends Command
{
	/**
	 * Initialisation des dépendances de la commande.
	 */
	public function __construct(
		private readonly MailerInterface $mailer,
		private readonly ParameterBagInterface $parameterBag,
		private readonly EntityManagerInterface $entityManager
	) {
		parent::__construct();
	}

	/**
	 * Création du courriel à envoyer.
	 */
	private function createEmail(int $count): Email
	{
		$recipient = $this->parameterBag->get('smtp.username');

		return (new Email())
			->to(new Address($recipient))
			->text(sprintf('There are %d report(s) to review.', $count))
			->subject('Report summary');
	}

	/**
	 * Envoi du courriel avec gestion des erreurs et journalisation.
	 */
	private function sendEmail(Email $email, SymfonyStyle $io, ?Message $signedEmail = null): int
	{
		try
		{
			$this->mailer->send($signedEmail ?? $email);

			$io->success('Email sent successfully.');

			return Command::SUCCESS;
		}
		catch (TransportExceptionInterface $error)
		{
			$io->error(sprintf('Failed to send email: %s', $error->getMessage()));

			return Command::FAILURE;
		}
	}

	/**
	 * Signature du courriel avec DKIM (si possible).
	 * @see https://symfony.com/doc/current/mailer.html#signing-messages
	 */
	private function signEmailWithDkim(Email $email, SymfonyStyle $io): ?Message
	{
		if (!$this->parameterBag->get('dkim.enabled'))
		{
			$io->warning('DKIM signing is disabled.');
			return null;
		}

		try {
			$signer = new DkimSigner(
				$this->parameterBag->get('dkim.private_key'),
				$this->parameterBag->get('dkim.domain'),
				$this->parameterBag->get('dkim.selector')
			);

			$io->success('Email signed with DKIM.');

			return $signer->sign($email);
		} catch (Exception $error) {
			$io->error(sprintf('Failed to sign email: %s', $error->getMessage()));
			return null;
		}
	}

	/**
	 * Exécution de la commande.
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$repository = $this->entityManager->getRepository(Report::class);
		$reports = $repository->findAll();
		$count = count($reports);

		if ($count === 0)
		{
			$output->writeln('No user reports found.');
			return Command::SUCCESS; // Pas d'erreur, juste pas de rapports à traiter.
		}

		$io = new SymfonyStyle($input, $output);
		$io->title(sprintf('Summary of %d user report(s)', $count));

		if (!$this->parameterBag->get('smtp.enabled'))
		{
			$io->error('SMTP is disabled.');
			return Command::SUCCESS; // Ce n'est pas une erreur, car le service est désactivé.
		}

		$email = $this->createEmail($count);
		$signedEmail = $this->signEmailWithDkim($email, $io);

		return $this->sendEmail($email, $io, $signedEmail);
	}
}