<?php

namespace App\Infrastructure\Security;

use App\Domain\Entity\AccessKey;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use App\Domain\Enum\AccessKeyCapability;
use App\Infrastructure\Repository\AccessKeyRepository;

/**
 * Keeps the active access keys in the application cache (Valkey in production), so the requests sending one
 *  cost no database query. Entries are found by the hash of the key and hold a CachedAccessKey.
 * Only active keys are cached: an unknown, expired or revoked key reaches the database each time, where
 *  the rate limiter counts the attempts, as caching it would let random values fill the cache.
 *  A malformed value is refused before any lookup.
 * It also keeps the browser origins declared by the active keys granting `private_api`, which the preflight
 *  requests are checked against, as they never carry the key.
 * A creation or a revocation forgets the entries at once (see AccessKeyService); an expiration is checked
 *  at each read.
 */
final readonly class AccessKeyCache
{
    private const KEY_PREFIX = 'access_key.';

    private const ORIGINS_KEY = 'access_key_origins';

    /**
     * Lifetime of an entry, in seconds: it bounds how long a key changed outside the API keeps its former state.
     */
    private const LIFETIME = 3600;

    public function __construct(
        private CacheItemPoolInterface $cache,
        private AccessKeyRepository $repository
    ) {
    }

    /**
     * Cache key of an access key, built from its hash: a cached entry never discloses the key.
     */
    private function getCacheKey(string $hash): string
    {
        return self::KEY_PREFIX . $hash;
    }

    /**
     * Finds the active key matching the clear value, from the cache or else from the database.
     * @throws InvalidArgumentException When the cache refuses the key, which never happens with a hash.
     */
    public function findActive(string $plainKey): ?CachedAccessKey
    {
        if (!SecretKey::isWellFormed($plainKey)) {
            return null;
        }

        $item = $this->cache->getItem($this->getCacheKey(SecretKey::hash($plainKey)));
        $cachedKey = $item->get();

        if (!$item->isHit() || !$cachedKey instanceof CachedAccessKey) {
            $accessKey = $this->repository->findOneByPlainKey($plainKey);

            if ($accessKey === null || !$accessKey->isActive()) {
                return null;
            }

            $cachedKey = CachedAccessKey::fromAccessKey($accessKey);

            $this->cache->save($item->set($cachedKey)->expiresAfter(self::LIFETIME));
        }

        return $cachedKey->isExpired() ? null : $cachedKey;
    }

    /**
     * Collects the origins of the active keys granting `private_api`, each with the latest expiration date
     *  of the keys declaring it (a Unix timestamp, null when one of them never expires).
     *
     * @return array<string, int|null>
     */
    private function collectDeclaredOrigins(): array
    {
        $origins = [];

        foreach ($this->repository->findActive() as $accessKey) {
            $grantsPrivateApi = in_array(AccessKeyCapability::PrivateApi->value, $accessKey->getCapabilities(), true);
            $expiresAt = $accessKey->getExpiresAt()?->getTimestamp();

            foreach ($grantsPrivateApi ? $accessKey->getAllowedOrigins() : [] as $origin) {
                $current = array_key_exists($origin, $origins) ? $origins[$origin] : 0;
                $neverExpires = $expiresAt === null || $current === null;

                $origins[$origin] = $neverExpires ? null : max($current, $expiresAt);
            }
        }

        return $origins;
    }

    /**
     * Tells whether an active key granting `private_api` declares the browser origin.
     * @throws InvalidArgumentException When the cache refuses the key, which never happens with a constant.
     */
    public function isDeclaredOrigin(string $origin): bool
    {
        $item = $this->cache->getItem(self::ORIGINS_KEY);
        $origins = $item->get();

        if (!$item->isHit() || !is_array($origins)) {
            $origins = $this->collectDeclaredOrigins();

            $this->cache->save($item->set($origins)->expiresAfter(self::LIFETIME));
        }

        if (!array_key_exists($origin, $origins)) {
            return false;
        }

        $expiresAt = $origins[$origin];

        return $expiresAt === null || (is_int($expiresAt) && $expiresAt > time());
    }

    /**
     * Drops the entries the key feeds, its own and the declared origins, so a creation or a revocation applies
     *  to the next request.
     * @throws InvalidArgumentException When the cache refuses the key, which never happens with a hash.
     */
    public function forget(AccessKey $accessKey): void
    {
        $this->cache->deleteItems([$this->getCacheKey($accessKey->getKey()), self::ORIGINS_KEY]);
    }
}
