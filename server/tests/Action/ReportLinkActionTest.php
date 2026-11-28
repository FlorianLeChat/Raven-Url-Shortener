<?php

namespace App\Tests\Action;

use App\Domain\Entity\Report;
use App\Tests\TestHelperTrait;
use App\Tests\Mock\DnsRecordResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests of `POST /v1/link/{id|slug}/report`.
 */
final class ReportLinkActionTest extends WebTestCase
{
    use TestHelperTrait;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        self::loadFixtures($this->client->getKernel()->getProjectDir());
    }

    public function testReportWithInvalidData(): void
    {
        $this->client->request('POST', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d0/report', [
            'email' => 'johndoe@' . DnsRecordResolver::NO_RECORD_HOST
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        self::assertStringContainsString('TOO_SHORT_ERROR', $content);
        self::assertStringContainsString('UNDELIVERABLE_ERROR', $content);
        self::assertStringContainsString('The domain of this email address cannot receive mail.', $content);
    }

    public function testReportWithExistingEmail(): void
    {
        $this->client->request('POST', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d5/report', [
            'email' => 'johndoe@domain.com',
            'reason' => 'This is a test'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertJsonStringEqualsJsonString('{"code":409,"message":"You have already reported this shortcut link, you cannot report it again."}', $content);
    }

    public function testReportTrustedLink(): void
    {
        $this->client->request('POST', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d6/report', [
            'reason' => 'Inappropriate content'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertJsonStringEqualsJsonString('{"code":403,"message":"You cannot report a trusted link. If you think this link is malicious, please contact an administrator."}', $content);
    }

    public function testReportMaximumReached(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->client->request('POST', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9/report', [
                'reason' => 'Inappropriate content ' . $i,
            ]);

            self::assertResponseIsSuccessful();
            self::assertJson($this->client->getResponse()->getContent());
        }

        $this->client->request('POST', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9/report', [
            'reason' => 'Inappropriate content',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_TOO_MANY_REQUESTS);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertJsonStringEqualsJsonString('{"code":429,"message":"The maximum number of reports for this link has been reached (3). It cannot be reported again."}', $content);
    }

    public function testReportSuccessfully(): void
    {
        $this->client->request('POST', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9/report', [
            'reason' => 'Inappropriate content',
        ], server: ['HTTP_ACCEPT_LANGUAGE' => 'fr-FR,fr;q=0.9']);

        self::assertResponseIsSuccessful();

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $report = $entityManager->find(Report::class, $content['id']);

        self::assertSame('pending', $content['status']);
        self::assertTrue($content['link']['reported']);
        self::assertSame('fr', $report?->getLocale());
    }
}
