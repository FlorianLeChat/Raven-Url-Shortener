<?php

namespace App\Tests\Action;

use Symfony\Component\Process\Process;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CheckSlugActionTest extends WebTestCase
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

    public function testSlugIsAvailable(): void
    {
        $this->client->request('POST', '/v1/slug', ['slug' => 'free-slug']);

        self::assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString('{"available":true}', $this->client->getResponse()->getContent());
    }

    public function testSlugIsNotAvailable(): void
    {
        $this->client->request('POST', '/v1/slug', ['slug' => 'test1']);

        self::assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString('{"available":false}', $this->client->getResponse()->getContent());
    }
}
