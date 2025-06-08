<?php

namespace App\Tests\Action;

use Symfony\Component\Process\Process;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test d'application pour l'action de vérification de la
 *  disponibilité d'un slug personnalisé.
 */
final class CheckSlugActionTest extends WebTestCase
{
	/**
	 * Client de navigation.
	 */
	protected KernelBrowser $client;

	/**
	 * Population de la base de données avec des données fictives.
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->client = static::createClient();

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

	/**
	 * Test pour vérifier la disponibilité d'un slug inexistant.
	 */
	public function testSlugIsAvailable(): void
	{
		$this->client->request('POST', '/api/v1/slug', ['slug' => 'free-slug']);

		$this->assertResponseIsSuccessful();
		$this->assertJsonStringEqualsJsonString('{"available":true}', $this->client->getResponse()->getContent());
	}

	/**
	 * Test pour vérifier la disponibilité d'un slug existant.
	 */
	public function testSlugIsNotAvailable(): void
	{
		$this->client->request('POST', '/api/v1/slug', ['slug' => 'test1']);

		$this->assertResponseIsSuccessful();
		$this->assertJsonStringEqualsJsonString('{"available":false}', $this->client->getResponse()->getContent());
	}
}