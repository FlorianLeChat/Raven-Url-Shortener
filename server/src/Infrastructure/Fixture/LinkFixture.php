<?php

namespace App\Infrastructure\Fixture;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Factory\LinkFactory;
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
		$link = new Link();
		$link->setId(new Uuid('0196cb17-b0f8-7e9c-b381-ef17aa05f3d9'));
		$link->setUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
		$link->setSlug('test1');
		$link->setVisitedAt(new DateTimeImmutable());

		$this->addReference("link1", $link);

		return $link;
	}

	/**
	 * Création d'un lien désactivé par un administrateur.
	 */
	private function createDisabledLink()
	{
		$link = new Link();
		$link->setId(new Uuid('0196cb17-b0f8-7e9c-b381-ef17aa05f3d0'));
		$link->setUrl('https://www.youtube.com/watch?v=oo46pJIPZtk');
		$link->setSlug('test2');
		$link->setEnabled(false);
		$link->setVisitedAt(new DateTimeImmutable('2014-01-01 00:00:00'));

		$this->addReference("link2", $link);

		return $link;
	}

	/**
	 * Création d'un lien signalé par un utilisateur.
	 */
	private function createReportedLink()
	{
		$link = new Link();
		$link->setId(new Uuid('0196cb17-b0f8-7e9c-b381-ef17aa05f3d5'));
		$link->setUrl('https://www.youtube.com/watch?v=VQRLujxTm3c');
		$link->setSlug('test3');
		$link->setVisitedAt(new DateTimeImmutable());

		$this->addReference("link3", $link);

		return $link;
	}

	/**
	 * Création d'un lien de confiance.
	 */
	private function createTrustedLink()
	{
		$link = new Link();
		$link->setId(new Uuid('0196cb17-b0f8-7e9c-b381-ef17aa05f3d6'));
		$link->setUrl('https://www.youtube.com/watch?v=HkJNRcQDE08');
		$link->setSlug('test4');
		$link->setTrusted(true);
		$link->setVisitedAt(new DateTimeImmutable());

		return $link;
	}

	/**
	 * Création d'un lien protégé par mot de passe.
	 */
	private function createPasswordProtectedLink()
	{
		$link = new Link();
		$link->setId(new Uuid('0196cb17-b0f8-7e9c-b381-ef17aa05f3d7'));
		$link->setUrl('https://www.youtube.com/watch?v=PjnXzeUSHUg');
		$link->setSlug('test5');
		$link->setPassword(password_hash('password123', PASSWORD_BCRYPT, ['cost' => 4]));
		$link->setVisitedAt(new DateTimeImmutable());

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
		$manager->persist($this->createTrustedLink());
		$manager->persist($this->createPasswordProtectedLink());
		$manager->flush();
	}
}