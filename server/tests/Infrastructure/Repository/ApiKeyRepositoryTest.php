<?php

namespace App\Tests\Infrastructure\Repository;

use App\Domain\Entity\Link;
use App\Domain\Entity\ApiKey;
use App\Tests\TestHelperTrait;
use App\Domain\Factory\ApiKeyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests of ApiKeyRepository on the fixtures.
 */
final class ApiKeyRepositoryTest extends KernelTestCase
{
    use TestHelperTrait;

    public function testSave(): void
    {
        $kernel = self::bootKernel();

        self::loadFixtures($kernel->getProjectDir());

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $repository = $entityManager->getRepository(ApiKey::class);

        $link = $entityManager->getRepository(Link::class)->findOneBy(['slug' => 'test4']);
        $apiKey = ApiKeyFactory::create($link);
        $link->setApiKey($apiKey);

        $repository->save($apiKey);

        self::assertNull($repository->findOneBy(['key' => $apiKey->getKey()]));

        $repository->save($apiKey, true);
        $entityManager->clear();

        self::assertNotNull($repository->findOneBy(['key' => $apiKey->getKey()]));
    }
}
