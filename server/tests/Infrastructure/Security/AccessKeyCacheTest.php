<?php

namespace App\Tests\Infrastructure\Security;

use Psr\Cache\CacheItemPoolInterface;
use App\Infrastructure\Security\SecretKey;
use App\Infrastructure\Security\AccessKeyCache;
use App\Infrastructure\Security\CachedAccessKey;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests of AccessKeyCache with entries written in the cache for keys the database does not hold.
 */
final class AccessKeyCacheTest extends KernelTestCase
{
    public function testFindActiveReadsTheCache(): void
    {
        $container = self::getContainer();
        $pool = $container->get(CacheItemPoolInterface::class);
        $accessKeyCache = $container->get(AccessKeyCache::class);

        $activeKey = SecretKey::generate();
        $expiredKey = SecretKey::generate();

        $pool->save($pool->getItem('access_key.' . SecretKey::hash($activeKey))->set(
            new CachedAccessKey('cached', ['trusted_links'], [], time() + 60)
        ));
        $pool->save($pool->getItem('access_key.' . SecretKey::hash($expiredKey))->set(
            new CachedAccessKey('expired', ['trusted_links'], [], time() - 60)
        ));

        self::assertSame('cached', $accessKeyCache->findActive($activeKey)?->id);
        self::assertNull($accessKeyCache->findActive($expiredKey));
        self::assertNull($accessKeyCache->findActive('malformed-key'));
    }
}
