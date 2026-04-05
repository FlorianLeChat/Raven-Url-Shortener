<?php

namespace App\Tests\Action;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UpdateLinkActionTest extends WebTestCase
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

    public function testPatchLinkWithMissingApiKey(): void
    {
        $this->client->request('PATCH', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":401,"message":"The API key is missing. Please provide it in the \"Authorization\" HTTP header."}', $content);
    }

    public function testPatchLinkWithInvalidApiKey(): void
    {
        $this->client->request('PATCH', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', server: [
            'HTTP_Authorization' => 'TEST'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":403,"message":"The provided API key is invalid. Please check it and try again."}', $content);
    }

    public function testPatchDisabledLink(): void
    {
        $this->client->request('PATCH', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d0', server: [
            'HTTP_Authorization' => 'Bearer i8tyoJjI3PUw+cqLdLCGipV6IPodANondBSqBkPzhfo='
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been disabled by its owner or by an administrator."}', $content);
    }

    public function testPatchReportedLink(): void
    {
        $this->client->request('PATCH', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d5', server: [
            'HTTP_Authorization' => 'Bearer wJY8ad9DVlKD+Sn4/ZjBALwI+qcFebozUFZnb2EFfBI='
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been reported by one or more users. This link must be verified by an administrator before it can be reached again."}', $content);
    }

    public function testPatchLinkWithInvalidData(): void
    {
        $this->client->request('PATCH', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', server: [
            'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
        ], content: json_encode([
            'field' => 'url',
            'value' => 'invalid_url'
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        $this->assertStringContainsString('INVALID_URL_ERROR', $content);
    }

    public function testPatchLinkWithDuplicatedSlug(): void
    {
        $this->client->request('PATCH', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', server: [
            'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
        ], content: json_encode([
            'field' => 'slug',
            'value' => 'test1'
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        $this->assertStringContainsString('DUPLICATE_SLUG_ERROR', $content);
    }

    public function testPatchLinkWithValidData(): void
    {
        $this->client->request('PATCH', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', server: [
            'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
        ], content: json_encode([
            'field' => 'url',
            'value' => 'https://www.google.com/'
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testPutLinkWithMissingApiKey(): void
    {
        $this->client->request('PUT', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":401,"message":"The API key is missing. Please provide it in the \"Authorization\" HTTP header."}', $content);
    }

    public function testPutLinkWithInvalidApiKey(): void
    {
        $this->client->request('PUT', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', server: [
            'HTTP_Authorization' => 'TEST'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":403,"message":"The provided API key is invalid. Please check it and try again."}', $content);
    }

    public function testPutDisabledLink(): void
    {
        $this->client->request('PUT', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d0', server: [
            'HTTP_Authorization' => 'Bearer i8tyoJjI3PUw+cqLdLCGipV6IPodANondBSqBkPzhfo='
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been disabled by its owner or by an administrator."}', $content);
    }

    public function testPutReportedLink(): void
    {
        $this->client->request('PUT', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d5', server: [
            'HTTP_Authorization' => 'Bearer wJY8ad9DVlKD+Sn4/ZjBALwI+qcFebozUFZnb2EFfBI='
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been reported by one or more users. This link must be verified by an administrator before it can be reached again."}', $content);
    }

    public function testPutLinkWithInvalidData(): void
    {
        $this->client->request('PUT', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', [
            'url' => 'invalid_url'
        ], server: [
            'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        $this->assertStringContainsString('INVALID_URL_ERROR', $content);
    }

    public function testPutLinkWithDuplicatedSlug(): void
    {
        $this->client->request('PUT', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', [
            'slug' => 'test2'
        ], server: [
            'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
        $this->assertStringContainsString('DUPLICATE_SLUG_ERROR', $content);
    }

    public function testPutLinkWithValidData(): void
    {
        $this->client->request('PUT', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', [
            'url' => 'https://www.google.com/'
        ], server: [
            'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
    }
}
