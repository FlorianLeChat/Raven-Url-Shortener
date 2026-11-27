<?php

namespace App\Tests\Action;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Tests\TestHelperTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests of `GET /v1/link/{id|slug}`.
 */
final class GetLinkDetailsActionTest extends WebTestCase
{
    use TestHelperTrait;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        self::loadFixtures($this->client->getKernel()->getProjectDir());
    }

    public function testGetExpiredLinkDetails(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $link = $entityManager->getRepository(Link::class)->findOneBy(['slug' => 'test5']);
        $link->setExpiresAt(new DateTimeImmutable('-1 hour'));
        $entityManager->flush();

        $this->client->request('GET', '/v1/link/test5');

        self::assertResponseStatusCodeSame(Response::HTTP_GONE);
        self::assertJsonStringEqualsJsonString(
            '{"code":410,"message":"The specified shortcut link has expired."}',
            $this->client->getResponse()->getContent()
        );
    }

    public function testGetLinkDetailsWithNoUrl(): void
    {
        $this->client->request('GET', '/v1/link');

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testGetLinkDetailsWithInvalidUuid(): void
    {
        $this->client->request('GET', '/v1/link/111b8c4-0a2d-4f3e-bb5f-7a9e6c3d8f1b');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetLinkDetailsWithInvalidSlug(): void
    {
        $this->client->request('GET', '/v1/link/slug-not-found');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetLinkDetailsWithMissingPassword(): void
    {
        $this->client->request('GET', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d7');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertJsonStringEqualsJsonString('{"code":401,"message":"The specified shortcut link is protected by a password. Please provide it in the \"Authorization\" HTTP header."}', $content);
    }

    public function testGetLinkDetailsWithInvalidPassword(): void
    {
        $this->client->request('GET', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d7', server: [
            'HTTP_Authorization' => 'Password haha'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertJsonStringEqualsJsonString('{"code":403,"message":"The password you provided for the shortcut link is invalid. Please check it and try again."}', $content);
    }

    public function testGetLinkDetailsWithValidUuid(): void
    {
        $this->client->request('GET', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9');

        self::assertResponseIsSuccessful();
        self::assertJson($this->client->getResponse()->getContent());
    }

    public function testGetLinkDetailsWithValidSlug(): void
    {
        $today = new DateTimeImmutable('today');

        $this->setVisitedAt($today);
        $this->client->request('GET', '/v1/link/test1');

        self::assertResponseIsSuccessful();
        self::assertJson($this->client->getResponse()->getContent());
        self::assertEquals($today, $this->getVisitedAt());

        $this->setVisitedAt(new DateTimeImmutable('-2 days'));
        $this->client->request('GET', '/v1/link/test1');

        self::assertResponseIsSuccessful();
        self::assertGreaterThanOrEqual($today, $this->getVisitedAt());
    }

    /**
     * Writes the last visit date of `test1` in the database.
     */
    private function setVisitedAt(DateTimeImmutable $visitedAt): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->getRepository(Link::class)->findOneBy(['slug' => 'test1'])->setVisitedAt($visitedAt);
        $entityManager->flush();
    }

    /**
     * Reads the last visit date of `test1` from the database.
     */
    private function getVisitedAt(): ?DateTimeImmutable
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        return $entityManager->getRepository(Link::class)->findOneBy(['slug' => 'test1'])->getVisitedAt();
    }

    public function testGetLinkDetailsWithValidPassword(): void
    {
        $this->client->request('GET', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d7', server: [
            'HTTP_Authorization' => 'Password password123'
        ]);

        self::assertResponseIsSuccessful();
        self::assertJson($this->client->getResponse()->getContent());
        self::assertResponseHeaderSame('Cache-Control', 'no-cache, private');
    }
}
