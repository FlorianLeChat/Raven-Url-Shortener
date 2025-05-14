<?php

namespace App\Infrastructure\Fixture;

use DateTime;
use App\Domain\Entity\Link;
use App\Domain\Entity\ApiKey;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Données de test pour les clés API de gestion des liens raccourcis.
 */
final class ApiKeyFixture extends Fixture  implements DependentFixtureInterface
{
	public function load(ObjectManager $manager): void
	{
		$link = $this->getReference("link", Link::class);

		$apiKey = new ApiKey();
		$apiKey->setLink($link);
		$apiKey->setCreatedAt(new DateTime());

		$manager->persist($apiKey);
		$manager->flush();
	}

	public function getDependencies(): array
	{
		return [
			LinkFixture::class
		];
	}
}