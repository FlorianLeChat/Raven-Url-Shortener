<?php

namespace App\Infrastructure\Fixture;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Entity\ApiKey;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

final class ApiKeyFixture extends Fixture implements DependentFixtureInterface
{
    private function getExpiresAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('2014-01-01 00:00:00');
    }

    private function createApiKeyWithoutIssue(): ApiKey
    {
        $apiKey = new ApiKey();
        $apiKey->setKey('7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28=');
        $apiKey->setLink($this->getReference("link1", Link::class));
        $apiKey->setExpiresAt($this->getExpiresAt());

        return $apiKey;
    }

    private function createDisabledApiKey(): ApiKey
    {
        $apiKey = new ApiKey();
        $apiKey->setKey('i8tyoJjI3PUw+cqLdLCGipV6IPodANondBSqBkPzhfo=');
        $apiKey->setLink($this->getReference("link2", Link::class));
        $apiKey->setExpiresAt($this->getExpiresAt());

        return $apiKey;
    }

    private function createReportedApiKey(): ApiKey
    {
        $apiKey = new ApiKey();
        $apiKey->setKey('wJY8ad9DVlKD+Sn4/ZjBALwI+qcFebozUFZnb2EFfBI=');
        $apiKey->setLink($this->getReference("link3", Link::class));
        $apiKey->setExpiresAt($this->getExpiresAt());

        return $apiKey;
    }

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createApiKeyWithoutIssue());
        $manager->persist($this->createDisabledApiKey());
        $manager->persist($this->createReportedApiKey());
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LinkFixture::class
        ];
    }
}
