<?php

namespace App\Infrastructure\Fixture;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Entity\ApiKey;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Données de test pour les clés API de gestion des liens raccourcis.
 */
final class ApiKeyFixture extends Fixture implements DependentFixtureInterface
{
	/**
	 * Récupération d'une date d'expiration fictive pour les clés API.
	 */
	private function getExpiresAt(): DateTimeImmutable
	{
		return new DateTimeImmutable('2014-01-01 00:00:00');
	}

	/**
	 * Création d'une clé API pour le lien raccourci sans problème.
	 */
	private function createApiKeyWithoutIssue()
	{
		$apiKey = new ApiKey();
		$apiKey->setKey('7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28=');
		$apiKey->setLink($this->getReference("link1", Link::class));
		$apiKey->setExpiresAt($this->getExpiresAt());

		return $apiKey;
	}

	/**
	 * Création d'une clé API pour le lien raccourci désactivé par un administrateur.
	 */
	private function createDisabledApiKey()
	{
		$apiKey = new ApiKey();
		$apiKey->setKey('i8tyoJjI3PUw+cqLdLCGipV6IPodANondBSqBkPzhfo=');
		$apiKey->setLink($this->getReference("link2", Link::class));
		$apiKey->setExpiresAt($this->getExpiresAt());

		return $apiKey;
	}

	/**
	 * Création d'une clé API pour le lien raccourci signalé par un utilisateur.
	 */
	private function createReportedApiKey()
	{
		$apiKey = new ApiKey();
		$apiKey->setKey('wJY8ad9DVlKD+Sn4/ZjBALwI+qcFebozUFZnb2EFfBI=');
		$apiKey->setLink($this->getReference("link3", Link::class));
		$apiKey->setExpiresAt($this->getExpiresAt());

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