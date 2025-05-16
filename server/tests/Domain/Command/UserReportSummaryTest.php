<?php

namespace App\Tests\Domain\Command;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Test de la commande de génération d'un résumé des signalements d'utilisateurs.
 */
final class UserReportSummaryTest extends KernelTestCase
{
	public function testExecute(): void
	{
		// Démarrage du noyau de l'application.
		$kernel = self::bootKernel();
		$application = new Application($kernel);

		// Initialisation de la base de données.
		$php = new PhpExecutableFinder();
		$process = new Process([
			$php->find(),
			sprintf('%s/bin/console', $kernel->getProjectDir()),
			'doctrine:fixtures:load',
			'--env=test',
			'--no-interaction'
		]);

		$process->disableOutput();
		$process->run();

		// Exécution de la commande.
		$command = $application->find('app:reports-summary');
		$tester = new CommandTester($command);
		$tester->execute([]);

		// Vérification de l'état de la commande.
		$tester->assertCommandIsSuccessful();

		// Vérification de la sortie de la commande.
		$output = $tester->getDisplay();

		$this->assertStringContainsString('Summary of 1 user report(s)', $output);
	}
}