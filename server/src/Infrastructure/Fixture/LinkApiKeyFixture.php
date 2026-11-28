<?php

namespace App\Infrastructure\Fixture;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Entity\LinkApiKey;
use Doctrine\Persistence\ObjectManager;
use App\Infrastructure\Security\SecretKey;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * API keys of the test links, all of them long expired. Tests send the clear values hashed here.
 */
final class LinkApiKeyFixture extends Fixture implements DependentFixtureInterface
{
    /**
     * Expiration date shared by every key, long past.
     */
    private function getExpiresAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2014-01-01 00:00:00');
    }

    /**
     * Key of `link1`, the link every successful update and deletion goes through.
     */
    private function createApiKeyWithoutIssue(): LinkApiKey
    {
        $apiKey = new LinkApiKey();
        $apiKey->setKey(SecretKey::hash('7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='));
        $apiKey->setLink($this->getReference("link1", Link::class));
        $apiKey->setExpiresAt($this->getExpiresAt());

        return $apiKey;
    }

    /**
     * Key of `link2`, the disabled link.
     */
    private function createDisabledApiKey(): LinkApiKey
    {
        $apiKey = new LinkApiKey();
        $apiKey->setKey(SecretKey::hash('i8tyoJjI3PUw+cqLdLCGipV6IPodANondBSqBkPzhfo='));
        $apiKey->setLink($this->getReference("link2", Link::class));
        $apiKey->setExpiresAt($this->getExpiresAt());

        return $apiKey;
    }

    /**
     * Key of `link3`, the reported link.
     */
    private function createReportedApiKey(): LinkApiKey
    {
        $apiKey = new LinkApiKey();
        $apiKey->setKey(SecretKey::hash('wJY8ad9DVlKD+Sn4/ZjBALwI+qcFebozUFZnb2EFfBI='));
        $apiKey->setLink($this->getReference("link3", Link::class));
        $apiKey->setExpiresAt($this->getExpiresAt());

        return $apiKey;
    }

    /**
     * Saves the three keys.
     */
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createApiKeyWithoutIssue());
        $manager->persist($this->createDisabledApiKey());
        $manager->persist($this->createReportedApiKey());
        $manager->flush();
    }

    /**
     * The keys need the links of LinkFixture.
     */
    public function getDependencies(): array
    {
        return [
            LinkFixture::class
        ];
    }
}
