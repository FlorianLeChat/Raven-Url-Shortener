<?php

namespace App\Tests\Action;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test d'application pour l'action de mise à jour partielle ou
 *  complète d'un lien raccourci.
 */
final class UpdateLinkActionTest extends WebTestCase
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
	 * Test d'une mise à jour partielle d'un lien raccourci sans clé API.
	 */
	public function testPatchLinkWithMissingApiKey(): void
	{
		$this->client->request('PATCH', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9');

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('MISSING_API_KEY_ERROR', $content);
	}

	/**
	 * Test d'une mise à jour partielle d'un lien raccourci avec une clé API invalide.
	 */
	public function testPatchLinkWithInvalidApiKey(): void
	{
		$this->client->request('PATCH', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', server: [
			'HTTP_Authorization' => 'TEST'
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":403,"message":"The provided API key is invalid. Please check it and try again."}', $content);
	}

	/**
	 * Test d'une mise à jour partielle d'un lien raccourci désactivé par un administrateur.
	 */
	public function testPatchDisabledLink()
	{
		$this->client->request('PATCH', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d0', server: [
			'HTTP_Authorization' => 'Bearer i8tyoJjI3PUw+cqLdLCGipV6IPodANondBSqBkPzhfo='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been disabled by its owner or by an administrator."}', $content);
	}

	/**
	 * Test d'une mise à jour partielle d'un lien raccourci signalé par un utilisateur.
	 */
	public function testPatchReportedLink()
	{
		$this->client->request('PATCH', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d5', server: [
			'HTTP_Authorization' => 'Bearer wJY8ad9DVlKD+Sn4/ZjBALwI+qcFebozUFZnb2EFfBI='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been reported by one or more users. This link must be verified by an administrator before it can be managed again."}', $content);
	}

	/**
	 * Test d'une mise à jour partielle d'un lien raccourci avec des données invalides.
	 */
	public function testPatchLinkWithInvalidData(): void
	{
		$this->client->request('PATCH', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', content: json_encode([
			'field' => 'url',
			'value' => 'invalid_url'
		]), server: [
			'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('INVALID_URL_ERROR', $content);
	}

	/**
	 * Test d'une mise à jour partielle d'un lien raccourci avec un slug dupliqué.
	 */
	public function testPatchLinkWithDuplicatedSlug(): void
	{
		$this->client->request('PATCH', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', content: json_encode([
			'field' => 'slug',
			'value' => 'test1'
		]), server: [
			'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('DUPLICATE_SLUG_ERROR', $content);
	}

	/**
	 * Test d'une mise à jour partielle d'un lien raccourci avec des données valides.
	 */
	public function testPatchLinkWithValidData(): void
	{
		$this->client->request('PATCH', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', content: json_encode([
			'field' => 'url',
			'value' => 'https://www.example.com'
		]), server: [
			'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertJson($this->client->getResponse()->getContent());
	}

	/**
	 * Test d'une mise à jour complète d'un lien raccourci sans clé API.
	 */
	public function testPutLinkWithMissingApiKey(): void
	{
		$this->client->request('PUT', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9');

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('MISSING_API_KEY_ERROR', $content);
	}

	/**
	 * Test d'une mise à jour complète d'un lien raccourci avec une clé API invalide.
	 */
	public function testPutLinkWithInvalidApiKey(): void
	{
		$this->client->request('PUT', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', server: [
			'HTTP_Authorization' => 'TEST'
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":403,"message":"The provided API key is invalid. Please check it and try again."}', $content);
	}

	/**
	 * Test d'une mise à jour complète d'un lien raccourci désactivé par un administrateur.
	 */
	public function testPutDisabledLink()
	{
		$this->client->request('PUT', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d0', server: [
			'HTTP_Authorization' => 'Bearer i8tyoJjI3PUw+cqLdLCGipV6IPodANondBSqBkPzhfo='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been disabled by its owner or by an administrator."}', $content);
	}

	/**
	 * Test d'une mise à jour complète d'un lien raccourci signalé par un utilisateur.
	 */
	public function testPutReportedLink()
	{
		$this->client->request('PUT', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d5', server: [
			'HTTP_Authorization' => 'Bearer wJY8ad9DVlKD+Sn4/ZjBALwI+qcFebozUFZnb2EFfBI='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":403,"message":"The specified shortcut link has been reported by one or more users. This link must be verified by an administrator before it can be managed again."}', $content);
	}

	/**
	 * Test d'une mise à jour complète d'un lien raccourci avec des données invalides.
	 */
	public function testPutLinkWithInvalidData(): void
	{
		$this->client->request('PUT', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', [
			'url' => 'invalid_url'
		], server: [
			'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('INVALID_URL_ERROR', $content);
	}

	/**
	 * Test d'une mise à jour complète d'un lien raccourci avec un slug dupliqué.
	 */
	public function testPutLinkWithDuplicatedSlug(): void
	{
		$this->client->request('PUT', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', [
			'slug' => 'test2'
		], server: [
			'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('DUPLICATE_SLUG_ERROR', $content);
	}

	/**
	 * Test d'une mise à jour complète d'un lien raccourci avec des données valides.
	 */
	public function testPutLinkWithValidData(): void
	{
		$this->client->request('PUT', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9', [
			'url' => 'https://www.example.com'
		], server: [
			'HTTP_Authorization' => 'Bearer 7hLtoMvpr3NDyd+l3FFeaZD68HGTffHniUUQPjwGy28='
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertJson($this->client->getResponse()->getContent());
	}
}