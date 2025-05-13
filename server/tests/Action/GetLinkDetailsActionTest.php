<?php

namespace App\Tests\Action;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test d'application pour l'action de récupération des
 *  informations d'un lien raccourci.
 */
final class GetLinkDetailsActionTest extends WebTestCase
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
	 * Test de récupération des informations d'un lien raccourci
	 *  sans fournir d'URL.
	 */
    public function testGetLinkDetailsWithNoUrl(): void
	{
		$this->client->request('GET', '/api/v1/link');

		$this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
	}

	/**
	 * Test de récupération des informations d'un lien raccourci
	 *  avec un UUID invalide.
	 */
    public function testGetLinkDetailsWithInvalidUuid(): void
	{
		$this->client->request('GET', '/api/v1/link/111b8c4-0a2d-4f3e-bb5f-7a9e6c3d8f1b');

		$this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}

	/**
	 * Test de récupération des informations d'un lien raccourci
	 *  avec un slug personnalisé invalide.
	 */
    public function testGetLinkDetailsWithInvalidSlug(): void
	{
		$this->client->request('GET', '/api/v1/link/slug-not-found');

		$this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}

	/**
	 * Test de récupération des informations d'un lien raccourci
	 *  avec un UUID valide.
	 */
    public function testGetLinkDetailsWithValidUuid(): void
	{
		$this->client->request('GET', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9');

		$this->assertResponseIsSuccessful();
		$this->assertJson($this->client->getResponse()->getContent());
	}

	/**
	 * Test de récupération des informations d'un lien raccourci
	 *  avec un slug personnalisé valide.
	 */
    public function testGetLinkDetailsWithValidSlug(): void
	{
		$this->client->request('GET', '/api/v1/link/test');

		$this->assertResponseIsSuccessful();
		$this->assertJson($this->client->getResponse()->getContent());
	}
}