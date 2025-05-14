<?php

namespace App\Infrastructure\Fixture;

use DateTime;
use App\Domain\Entity\Link;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

/**
 * Données de test pour les liens raccourcis.
 */
final class LinkFixture extends Fixture
{
	/**
	 * Création d'un lien sans problème.
	 */
	private function createLinkWithoutIssue()
	{
		$uuid = new Uuid('0196cb17-b0f8-7e9c-b381-ef17aa05f3d9');
		$currentDate = new DateTime();

		$link = new Link();
		$link->setId($uuid);
		$link->setUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
		$link->setSlug('test1');
		$link->setCreatedAt($currentDate);
		$link->setVisitedAt($currentDate);

		$this->addReference("link1", $link);

		return $link;
	}

	/**
	 * Création d'un lien désactivé par un administrateur.
	 */
	private function createDisabledLink()
	{
		$uuid = new Uuid('0196cb17-b0f8-7e9c-b381-ef17aa05f3d0');
		$currentDate = new DateTime();

		$link = new Link();
		$link->setId($uuid);
		$link->setUrl('https://www.youtube.com/watch?v=oo46pJIPZtk');
		$link->setSlug('test2');
		$link->setEnabled(false);
		$link->setCreatedAt($currentDate);
		$link->setVisitedAt($currentDate);

		$this->addReference("link2", $link);

		return $link;
	}

	/**
	 * Création d'un lien signalé par un utilisateur.
	 */
	private function createReportedLink()
	{
		$uuid = new Uuid('0196cb17-b0f8-7e9c-b381-ef17aa05f3d5');
		$currentDate = new DateTime();

		$link = new Link();
		$link->setId($uuid);
		$link->setUrl('https://www.youtube.com/watch?v=VQRLujxTm3c');
		$link->setSlug('test3');
		$link->setCreatedAt($currentDate);
		$link->setVisitedAt($currentDate);

		$this->addReference("link3", $link);

		return $link;
	}

	/**
	 * Création des liens raccourcis et ajout à la base de données.
	 */
	public function load(ObjectManager $manager): void
	{
		$manager->persist($this->createLinkWithoutIssue());
		$manager->persist($this->createDisabledLink());
		$manager->persist($this->createReportedLink());
		$manager->flush();
	}
}