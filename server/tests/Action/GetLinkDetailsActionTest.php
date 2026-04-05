<?php

namespace App\Tests\Action;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GetLinkDetailsActionTest extends WebTestCase
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

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":401,"message":"The specified shortcut link is protected by a password. Please provide it in the \"Authorization\" HTTP header."}', $content);
    }

    public function testGetLinkDetailsWithInvalidPassword(): void
    {
        $this->client->request('GET', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d7', server: [
            'HTTP_Authorization' => 'Password haha'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $content = $this->client->getResponse()->getContent();

        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString('{"code":403,"message":"The password you provided for the shortcut link is invalid. Please check it and try again."}', $content);
    }

    public function testGetLinkDetailsWithValidUuid(): void
    {
        $this->client->request('GET', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9');

        self::assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testGetLinkDetailsWithValidSlug(): void
    {
        $this->client->request('GET', '/v1/link/test1');

        self::assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function testGetLinkDetailsWithValidPassword(): void
    {
        $this->client->request('GET', '/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d7', server: [
            'HTTP_Authorization' => 'Password password123'
        ]);

        self::assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());
    }
}
