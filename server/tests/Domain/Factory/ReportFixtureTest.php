<?php

namespace App\Tests\Domain\Factory;

use PHPUnit\Framework\TestCase;
use App\Domain\Factory\ReportFactory;

/**
 * Test de la fabrique de signalements de liens raccourcis.
 */
final class ReportFixtureTest extends TestCase
{
	/**
	 * Test de la création d'un signalement utilisateur.
	 */
	public function testCreateUserReport(): void
	{
		$report = ReportFactory::create($this->createMock('App\Domain\Entity\Link'), 'Spam', 'test@domain.com');

		$this->assertInstanceOf('App\Domain\Entity\Report', $report);

		// Identifiant unique.
		$this->assertInstanceOf('Symfony\Component\Uid\Uuid', $report->getId());

		// Lien raccourci associé.
		$this->assertInstanceOf('App\Domain\Entity\Link', $report->getLink());

		// Adresse e-mail.
		$this->assertEquals('test@domain.com', $report->getEmail());

		// Raison du signalement.
		$this->assertEquals('Spam', $report->getReason());

		// Dates de création et de dernière mise à jour.
		$this->assertNotNull($report->getCreatedAt());
		$this->assertNull($report->getUpdatedAt());
	}
}