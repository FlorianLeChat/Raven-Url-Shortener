<?php

namespace App\Infrastructure\Fixture;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Entity\Report;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

/**
 * Données de test pour les signalements de liens raccourcis.
 */
final class ReportFixture extends Fixture
{
	public function load(ObjectManager $manager): void
	{
		$link = $this->getReference("link3", Link::class);

		$report = new Report();
		$report->setEmail('johndoe@domain.com');
		$report->setReason('Spam');
		$report->setLink($link);

		$manager->persist($report);
		$manager->flush();
	}
}