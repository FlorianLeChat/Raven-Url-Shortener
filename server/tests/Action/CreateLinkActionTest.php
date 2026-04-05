<?php

namespace App\Tests\Action;

use DateTime;
use DateTimeInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateLinkActionTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $php = new PhpExecutableFinder();
        $process = new Process([
            $php->find(),
            sprintf('%s/bin/console', $this->client->getKernel()->getProjectDir()),
            'doctrine:fixtures:load',
            '--env=test',
            '--no-interaction'
        ]);

        $process->disableOutput();
        $process->run();
    }

    public function testCreateLinkWithNoUrl(): void
    {
        $this->client->request('POST', '/v1/link');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        $this->assertStringContainsString('IS_BLANK_ERROR', $content);
    }

    public function testCreateLinkWithExistingSlug(): void
    {
        $this->client->request('POST', '/v1/link', [
            'url' => 'https://www.google.com/',
            'slug' => 'test1'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        $this->assertStringContainsString('DUPLICATE_SLUG_ERROR', $content);
    }

    public function testCreateLinkWithInvalidExpirationDate(): void
    {
        $this->client->request('POST', '/v1/link', [
            'url' => 'https://www.google.com/',
            'expiration' => '2023-10-01T00:00:00+00:00'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        $this->assertStringContainsString('NOT_IN_RANGE_ERROR', $content);

        $this->client->request('POST', '/v1/link', [
            'url' => 'https://www.google.com/',
            'expiration' => '2034-10-01T00:00:00+00:00'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
    }

    public function testCreateLinkWithUnreachableUrl(): void
    {
        $this->client->request('POST', '/v1/link', [
            'url' => 'https://unreachable.example.com'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        $this->assertStringContainsString('UNREACHABLE_URL_ERROR', $content);
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
        $this->assertJson($this->client->getResponse()->getContent());
    }
}
