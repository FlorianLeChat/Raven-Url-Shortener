<?php

namespace App\Tests\Action;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DeleteLinkActionTest extends WebTestCase
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

    public function testDeleteLinkWithMissingApiKey(): void
    {
        $this->client->request('DELETE', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":401,"message":"The API key is missing. Please provide it in the \"Authorization\" HTTP header."}', $content);
    }

    public function testDeleteLinkWithInvalidApiKey(): void
    {
        $this->client->request('DELETE', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', server: [
            'HTTP_Authorization' => 'TEST'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":403,"message":"The provided API key is invalid. Please check it and try again."}', $content);
    }

    public function testDeleteDisabledLink(): void
    {
        $this->client->request('DELETE', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d0', server: [
            'HTTP_Authorization' => 'Bearer i8tyoJjI3PUw+cqLdLCGipV6IPodANondBSqBkPzhfo='
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been disabled by its owner or by an administrator."}', $content);
    }

    public function testDeleteReportedLink(): void
    {
        $this->client->request('DELETE', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d5', server: [
            'HTTP_Authorization' => 'Bearer wJY8ad9DVlKD+Sn4/ZjBALwI+qcFebozUFZnb2EFfBI='
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been reported by one or more users. This link must be verified by an administrator before it can be reached again."}', $content);
    }

    public function testDeleteLinkWithInvalidUuid(): void
    {
        $this->client->request('DELETE', '/v1/link/phpunit', server: [
            'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteLinkWithSuccess(): void
    {
        $this->client->request('DELETE', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', server: [
            'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
