<?php

namespace App\Tests\Action;

use App\Domain\Entity\Report;
use App\Tests\TestHelperTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests of `PATCH /v1/report/{id}`, authenticated by the `ADMIN_API_KEY` of `phpunit.xml`.
 */
final class ReviewReportActionTest extends WebTestCase
{
    use TestHelperTrait;

    private const ADMIN_API_KEY = 'test_admin_api_key';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        self::loadFixtures($this->client->getKernel()->getProjectDir());
    }

    /**
     * Sends the review of the report of ReportFixture, against `test3`, with the given key and body.
     *
     * @param array<string, mixed> $body
     */
    private function reviewFixtureReport(array $body, ?string $apiKey = self::ADMIN_API_KEY): string
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $report = $entityManager->getRepository(Report::class)->findOneBy(['email' => 'johndoe@domain.com']);
        $headers = $apiKey === null ? [] : ['HTTP_AUTHORIZATION' => 'Bearer ' . $apiKey];

        $this->client->request('PATCH', '/v1/report/' . $report?->getId(), $body, server: $headers);

        return (string) $this->client->getResponse()->getContent();
    }

    public function testReviewWithoutApiKey(): void
    {
        $content = $this->reviewFixtureReport(['status' => 'accepted'], null);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertResponseHeaderSame('WWW-Authenticate', 'Bearer');
        self::assertJsonStringEqualsJsonString('{"code":401,"message":"The API key is missing. Please provide it in the \"Authorization\" HTTP header."}', $content);

        $content = $this->reviewFixtureReport(['status' => 'accepted'], 'wrong_key');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertJsonStringEqualsJsonString('{"code":403,"message":"The provided API key is invalid. Please check it and try again."}', $content);
    }

    public function testReviewWithInvalidStatus(): void
    {
        $content = $this->reviewFixtureReport(['status' => 'pending']);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertStringContainsString('"status":[{"code":"NO_SUCH_CHOICE_ERROR","value":"pending"', $content);
        self::assertStringContainsString('The value you selected is not a valid choice.', $content);
    }

    public function testAcceptReport(): void
    {
        $content = $this->reviewFixtureReport(['status' => 'accepted']);

        self::assertResponseIsSuccessful();

        $report = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('accepted', $report['status']);
        self::assertNotNull($report['reviewedAt']);
        self::assertFalse($report['link']['enabled']);
        self::assertFalse($report['link']['reported']);
    }

    public function testRejectReportOnce(): void
    {
        $content = $this->reviewFixtureReport(['status' => 'rejected', 'trusted' => true]);

        self::assertResponseIsSuccessful();

        $report = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('rejected', $report['status']);
        self::assertTrue($report['link']['enabled']);
        self::assertTrue($report['link']['trusted']);
        self::assertFalse($report['link']['reported']);

        $content = $this->reviewFixtureReport(['status' => 'accepted']);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        self::assertJsonStringEqualsJsonString('{"code":409,"message":"This report has already been reviewed by an administrator."}', $content);
    }
}
