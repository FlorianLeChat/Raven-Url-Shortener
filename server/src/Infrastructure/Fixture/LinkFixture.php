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
	public function load(ObjectManager $manager): void
	{
		$uuid = new Uuid('0196cb17-b0f8-7e9c-b381-ef17aa05f3d9');
		$currentDate = new DateTime();

		$link = new Link();
		$link->setId($uuid);
		$link->setUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
		$link->setSlug('test');
		$link->setCreatedAt($currentDate);
		$link->setVisitedAt($currentDate);

		$manager->persist($link);
		$manager->flush();

		$this->addReference("link", $link);
	}
}