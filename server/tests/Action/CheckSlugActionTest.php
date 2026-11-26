<?php

namespace App\Tests\Action;

use App\Tests\TestHelperTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests of `POST /v1/slug`.
 */
final class CheckSlugActionTest extends WebTestCase
{
    use TestHelperTrait;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        self::loadFixtures($this->client->getKernel()->getProjectDir());
    }

    public function testSlugIsAvailable(): void
    {
        $this->client->request('POST', '/v1/slug', ['slug' => 'free-slug']);

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('{"available":true}', $this->client->getResponse()->getContent());
    }

    public function testSlugIsNotAvailable(): void
    {
        $this->client->request('POST', '/v1/slug', ['slug' => 'test1']);

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString('{"available":false}', $this->client->getResponse()->getContent());
    }
}
