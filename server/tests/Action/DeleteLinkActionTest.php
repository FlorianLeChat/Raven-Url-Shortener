<?php

namespace App\Tests\Action;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test d'application pour l'action de suppression d'un lien raccourci.
 */
final class DeleteLinkActionTest extends WebTestCase
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
	 * Test de suppression d'un lien raccourci sans clé API.
	 */
	public function testDeleteLinkWithMissingApiKey(): void
	{
		$this->client->request('DELETE', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9');

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('MISSING_API_KEY_ERROR', $content);
	}

	/**
	 * Test de suppression d'un lien raccourci avec une clé API invalide.
	 */
	public function testDeleteLinkWithInvalidApiKey(): void
	{
		$this->client->request('DELETE', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', server: [
			'HTTP_Authorization' => 'TEST'
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":403,"message":"The provided API key is invalid. Please check it and try again."}', $content);
	}

	/**
	 * Test de suppression d'un lien raccourci désactivé par un administrateur.
	 */
	public function testDeleteDisabledLink()
	{
		$this->client->request('DELETE', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d0', server: [
			'HTTP_Authorization' => 'Bearer i8tyoJjI3PUw+cqLdLCGipV6IPodANondBSqBkPzhfo='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been disabled by its owner or by an administrator."}', $content);
	}

	/**
	 * Test de suppression d'un lien raccourci signalé par un utilisateur.
	 */
	public function testDeleteReportedLink()
	{
		$this->client->request('DELETE', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d5', server: [
			'HTTP_Authorization' => 'Bearer wJY8ad9DVlKD+Sn4/ZjBALwI+qcFebozUFZnb2EFfBI='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been reported by one or more users. This link must be verified by an administrator before it can be managed again."}', $content);
	}

	/**
	 * Test de suppression d'un lien raccourci avec des données invalides.
	 */
	public function testDeleteLinkWithInvalidUuid(): void
	{
		$this->client->request('DELETE', '/api/v1/link/phpunit', server: [
			'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
	}

	/**
	 * Test de suppression réussie d'un lien raccourci.
	 */
	public function testDeleteLinkWithSuccess(): void
	{
		$this->client->request('DELETE', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', server: [
			'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
	}
}