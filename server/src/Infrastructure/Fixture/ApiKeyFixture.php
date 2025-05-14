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
	/**
	 * Création d'une clé API pour le lien raccourci sans problème.
	 */
	private function createApiKeyWithoutIssue()
	{
		$apiKey = new ApiKey();
		$apiKey->setKey('c7e8f5b19f2341b59d7b0a0dc33c3e847a9d4f6ae95e3c738f621a1296bcf207');
		$apiKey->setLink($this->getReference("link1", Link::class));
		$apiKey->setCreatedAt(new DateTime());

		return $apiKey;
	}

	/**
	 * Création d'une clé API pour le lien raccourci désactivé par un administrateur.
	 */
	private function createDisabledApiKey()
	{
		$apiKey = new ApiKey();
		$apiKey->setKey('f4a93c5d2be6e78a46b3fe190a5c8df1e209b6ce03d84224a80e6f1f5cb34b79');
		$apiKey->setLink($this->getReference("link2", Link::class));
		$apiKey->setCreatedAt(new DateTime());

		return $apiKey;
	}

	/**
	 * Création d'une clé API pour le lien raccourci signalé par un utilisateur.
	 */
	private function createReportedApiKey()
	{
		$apiKey = new ApiKey();
		$apiKey->setKey('e1d2a8f7944b134de782a4b5f10a0c9b9d3b7f8ee649ce13a6fa2e3d294f3170');
		$apiKey->setLink($this->getReference("link3", Link::class));
		$apiKey->setCreatedAt(new DateTime());

		return $apiKey;
	}

	/**
	 * Création des clés API et ajout à la base de données.
	 */
	public function load(ObjectManager $manager): void
	{
		$manager->persist($this->createApiKeyWithoutIssue());
		$manager->persist($this->createDisabledApiKey());
		$manager->persist($this->createReportedApiKey());
		$manager->flush();
	}

	/**
	 * Déclaration des dépendances pour les données de test.
	 */
	public function getDependencies(): array
	{
		return [
			LinkFixture::class
		];
	}
}