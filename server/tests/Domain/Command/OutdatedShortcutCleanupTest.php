<?php

namespace App\Tests\Domain\Command;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Test de la commande de suppression des liens raccourcis expirés.
 */
final class OutdatedShortcutCleanupTest extends KernelTestCase
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
		$command = $application->find('app:shortcut-cleanup');
		$tester = new CommandTester($command);
		$tester->execute([]);

		// Vérification de l'état de la commande.
		$tester->assertCommandIsSuccessful();

		// Vérification de la sortie de la commande.
		$output = $tester->getDisplay();

		$this->assertStringContainsString('[OK] Deleted 1 expired shortcut link(s).', $output);
	}
}