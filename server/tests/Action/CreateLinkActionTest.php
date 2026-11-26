<?php

namespace App\Tests\Action;

use DateTime;
use DateTimeInterface;
use App\Tests\TestHelperTrait;
use App\Tests\Mock\HttpClientResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests of `POST /v1/link`.
 */
final class CreateLinkActionTest extends WebTestCase
{
    use TestHelperTrait;

    private const API_KEY_PATTERN = '/^.{43}=$/';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        self::loadFixtures($this->client->getKernel()->getProjectDir());
    }

    public function testCreateLinkWithNoUrl(): void
    {
        $this->client->request('POST', '/v1/link');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        self::assertStringContainsString('IS_BLANK_ERROR', $content);
    }

    public function testCreateLinkWithExistingSlug(): void
    {
        $this->client->request('POST', '/v1/link', [
            'url' => 'https://www.google.com/',
            'slug' => 'test1'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        self::assertStringContainsString('DUPLICATE_SLUG_ERROR', $content);
    }

    public function testCreateLinkWithInvalidExpirationDate(): void
    {
        $this->client->request('POST', '/v1/link', [
            'url' => 'https://www.google.com/',
            'expiration' => '2023-10-01T00:00:00+00:00'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        self::assertStringContainsString('NOT_IN_RANGE_ERROR', $content);

        $this->client->request('POST', '/v1/link', [
            'url' => 'https://www.google.com/',
            'expiration' => '2034-10-01T00:00:00+00:00'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
    }

    public function testCreateLinkWithUnreachableUrl(): void
    {
        $this->client->request('POST', '/v1/link', [
            'url' => 'https://' . HttpClientResponseFactory::UNREACHABLE_HOST
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        self::assertStringContainsString('UNREACHABLE_URL_ERROR', $content);
    }

    public function testCreateLinkSuccessfully(): void
    {
        $date = new DateTime('+1 week');

        $this->client->request('POST', '/v1/link', [
            'url' => 'https://www.google.com/',
            'slug' => 'custom-slug',
            'expiration' => $date->format(DateTimeInterface::RFC3339)
        ]);

        self::assertResponseIsSuccessful();
        self::assertJson($this->client->getResponse()->getContent());
    }

    public function testCreateLinkWithoutApiManagement(): void
    {
        $this->client->request('POST', '/v1/link', [
            'url' => 'https://www.google.com/'
        ]);

        self::assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);

        self::assertNull($content['apiKey']);
    }

    public function testCreateLinkWithApiManagement(): void
    {
        $this->client->request('POST', '/v1/link', [
            'url' => 'https://www.google.com/',
            'api-management' => 'true'
        ]);

        self::assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);

        self::assertMatchesRegularExpression(self::API_KEY_PATTERN, $content['apiKey']);
    }

    public function testCreateLinkWithFailedUrl(): void
    {
        $this->client->request('POST', '/v1/link', [
            'url' => 'https://' . HttpClientResponseFactory::NOT_FOUND_HOST . '/page'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        self::assertStringContainsString('UNREACHABLE_URL_ERROR', $content);
        self::assertStringContainsString('The specified URL is unreachable.', $content);
    }

    public function testCreateLinkWithBlockedUrl(): void
    {
        $this->client->request('POST', '/v1/link', [
            'url' => 'https://' . HttpClientResponseFactory::FORBIDDEN_HOST . '/page'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testCreateLinkWithPassword(): void
    {
        $this->client->request('POST', '/v1/link', [
            'url' => 'https://www.google.com/',
            'slug' => 'protected-slug',
            'password' => 'password123'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->client->request('GET', '/v1/link/protected-slug');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
