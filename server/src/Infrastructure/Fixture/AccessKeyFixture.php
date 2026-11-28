<?php

namespace App\Infrastructure\Fixture;

use DateTimeImmutable;
use App\Domain\Entity\AccessKey;
use Doctrine\Persistence\ObjectManager;
use App\Domain\Enum\AccessKeyCapability;
use App\Infrastructure\Security\SecretKey;
use Doctrine\Bundle\FixturesBundle\Fixture;

/**
 * Access keys of the tests, found by their name. Tests send the clear values hashed here.
 */
final class AccessKeyFixture extends Fixture
{
    /**
     * Builds a key from its clear value.
     *
     * @param list<string> $capabilities
     */
    private function createAccessKey(string $name, string $plainKey, array $capabilities): AccessKey
    {
        $accessKey = new AccessKey();
        $accessKey->setName($name);
        $accessKey->setKey(SecretKey::hash($plainKey));
        $accessKey->setCapabilities($capabilities);

        return $accessKey;
    }

    /**
     * Saves four keys: one granting every capability without expiration date, one granting only `trusted_links`,
     *  one expired and one revoked, both granting every capability. Each of the first three declares
     *  a browser origin named after it.
     */
    public function load(ObjectManager $manager): void
    {
        $capabilities = AccessKeyCapability::values();

        $fullKey = $this->createAccessKey('full', 'ysvT1q67SCh7h+jAqC/gbrISbIMsuCBONZL4sK8/Urs=', $capabilities);
        $fullKey->setAllowedOrigins(['https://full.example.com']);

        $trustedKey = $this->createAccessKey('trusted', '91NknkOqck0+Jv0/7NUBBfixUKEjPwfY4ADVG4EmJpw=', [
            AccessKeyCapability::TrustedLinks->value
        ]);
        $trustedKey->setAllowedOrigins(['https://trusted.example.com']);
        $trustedKey->setExpiresAt(new DateTimeImmutable('+6 months'));

        $expiredKey = $this->createAccessKey('expired', 'hbUS0gq8vnS1We2OZXeEntKRHcIfyTxV6daeVYLg2IA=', $capabilities);
        $expiredKey->setAllowedOrigins(['https://expired.example.com']);
        $expiredKey->setExpiresAt(new DateTimeImmutable('2014-01-01 00:00:00'));

        $revokedKey = $this->createAccessKey('revoked', '8Tn5q3RP4gg//sM0VrmKpAjDnacq0VfYxXWvB1db81I=', $capabilities);
        $revokedKey->setRevokedAt(new DateTimeImmutable('2020-01-01 00:00:00'));

        $manager->persist($fullKey);
        $manager->persist($trustedKey);
        $manager->persist($expiredKey);
        $manager->persist($revokedKey);
        $manager->flush();
    }
}
