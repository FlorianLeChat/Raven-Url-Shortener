<?php

namespace App\Tests\Action;

use DateTime;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test d'application pour l'action de création de liens
 *  raccourcis.
 */
final class CreateLinkActionTest extends WebTestCase
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
	 * Test de création d'un lien raccourci sans fournir d'URL.
	 */
	public function testCreateLinkWithNoUrl(): void
	{
		$this->client->request('POST', '/api/v1/link');

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('IS_BLANK_ERROR', $content);
	}

	/**
	 * Test de création d'un lien raccourci avec un slug personnalisé existant.
	 */
	public function testCreateLinkWithExistingSlug(): void
	{
		$this->client->request('POST', '/api/v1/link', [
			'url' => 'https://www.google.com/',
			'slug' => 'test1'
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('DUPLICATE_SLUG_ERROR', $content);
	}

	/**
	 * Test de création d'un lien raccourci avec des dates d'expiration
	 *  trop anciennes ou trop récentes.
	 */
	public function testCreateLinkWithInvalidExpirationDate(): void
	{
		// Date trop ancienne.
		$this->client->request('POST', '/api/v1/link', [
			'url' => 'https://www.google.com/',
			'expiration' => '2023-10-01T00:00:00+00:00'
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('NOT_IN_RANGE_ERROR', $content);

		// Date trop récente.
		$this->client->request('POST', '/api/v1/link', [
			'url' => 'https://www.google.com/',
			'expiration' => '2034-10-01T00:00:00+00:00'
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
	}

	/**
	 * Test de création d'un lien raccourci avec une URL
	 *  inaccessible sur Internet.
	 */
	public function testCreateLinkWithUnreachableUrl(): void
	{
		$this->client->request('POST', '/api/v1/link', [
			'url' => 'https://unreachable.example.com'
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('UNREACHABLE_URL_ERROR', $content);
	}

	/**
	 * Test de création d'un lien raccourci avec succès.
	 */
	public function testCreateLinkSuccessfully(): void
	{
		$date = new DateTime('+1 week');

		$this->client->request('POST', '/api/v1/link', [
			'url' => 'https://www.google.com/',
			'slug' => 'custom-slug',
			'expiration' => $date->format(DateTime::RFC3339)
		]);

		$this->assertResponseIsSuccessful();
		$this->assertJson($this->client->getResponse()->getContent());
	}
}