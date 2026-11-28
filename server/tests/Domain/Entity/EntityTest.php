<?php

namespace App\Tests\Domain\Entity;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Entity\Report;
use Symfony\Component\Uid\Uuid;
use PHPUnit\Framework\TestCase;
use App\Domain\Entity\LinkApiKey;

/**
 * Tests of the accessors and the serialization of the Link, LinkApiKey and Report entities.
 */
final class EntityTest extends TestCase
{
    /**
     * Creates a link holding only the values `toArray()` needs.
     */
    private function createLink(): Link
    {
        $link = new Link();
        $link->setUrl('https://example.com');
        $link->setSlug('example');

        return $link;
    }

    public function testLink(): void
    {
        $id = Uuid::v7();
        $date = new DateTimeImmutable('2020-01-01 00:00:00');
        $link = $this->createLink();
        $link->setId($id);
        $link->setCreatedAt($date);
        $link->setUpdatedAt($date);
        $link->setEnabled(false);

        $data = $link->toArray();

        self::assertSame($id, $data['id']);
        self::assertSame($date, $data['createdAt']);
        self::assertSame($date, $data['updatedAt']);
        self::assertFalse($data['enabled']);
        self::assertFalse($data['reported']);
        self::assertNull($data['apiKey']);
    }

    public function testLinkDisclosesOnlyPlainApiKey(): void
    {
        $link = $this->createLink();
        $apiKey = (new LinkApiKey())->setKey('hash')->setLink($link);
        $link->setApiKey($apiKey);

        self::assertNull($link->toArray()['apiKey']);

        $apiKey->setPlainKey('key');

        self::assertSame('key', $link->toArray()['apiKey']);
    }

    public function testApiKey(): void
    {
        $date = new DateTimeImmutable('2020-01-01 00:00:00');
        $link = $this->createLink();
        $apiKey = new LinkApiKey();
        $apiKey->setKey('key');
        $apiKey->setLink($link);
        $apiKey->setCreatedAt($date);
        $apiKey->setUpdatedAt($date);
        $apiKey->setExpiresAt($date);

        $data = $apiKey->toArray();

        self::assertSame($apiKey->getId(), $data['id']);
        self::assertSame('key', $data['key']);
        self::assertSame('example', $data['link']['slug']);
        self::assertSame($date, $data['createdAt']);
        self::assertSame($date, $data['updatedAt']);
        self::assertSame($date, $data['expiresAt']);
        self::assertSame($link, $apiKey->getLink());
    }

    public function testReport(): void
    {
        $date = new DateTimeImmutable('2020-01-01 00:00:00');
        $link = $this->createLink();
        $report = new Report();
        $report->setLink($link);
        $report->setReason('Spam content');
        $report->setCreatedAt($date);
        $report->setUpdatedAt($date);

        $data = $report->toArray();

        self::assertSame($report->getId(), $data['id']);
        self::assertSame('example', $data['link']['slug']);
        self::assertSame('Spam content', $data['reason']);
        self::assertNull($data['email']);
        self::assertSame('pending', $data['status']);
        self::assertNull($data['reviewedAt']);
        self::assertSame('en', $report->getLocale());
        self::assertSame($date, $data['createdAt']);
        self::assertSame($date, $data['updatedAt']);
        self::assertSame($link, $report->getLink());
    }
}
