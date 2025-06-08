<?php

namespace App\Tests\Action;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test d'application pour l'action de création d'un signalement
 *  pour un lien raccourci.
 */
final class ReportLinkActionTest extends WebTestCase
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
	 * Test de création d'un signalement sans fournir de raison.
	 */
	public function testReportWithNoReason(): void
	{
		$this->client->request('POST', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d0/report');

		$this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertStringStartsWith('{"code":400,"message":"An error occurred during data validation."', $content);
		$this->assertStringContainsString('TOO_SHORT_ERROR', $content);
	}

	/**
	 * Test de création d'un signalement avec une adresse
	 *  électronique d'un utilisateur ayant déjà signalé le lien.
	 */
	public function testReportWithExistingEmail(): void
	{
		$this->client->request('POST', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d5/report', [
			'email' => 'johndoe@domain.com',
			'reason' => 'This is a test'
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":409,"message":"You have already reported this shortcut link, you cannot report it again."}', $content);
	}

	/**
	 * Test de création d'un signalement pour un lien désactivé.
	 */
	public function testReportTrustedLink(): void
	{
		$this->client->request('POST', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d6/report', [
			'reason' => 'Inappropriate content'
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":403,"message":"You cannot report a trusted link. If you think this link is malicious, please contact an administrator."}', $content);
	}

	/**
	 * Test de création d'un signalement pour un lien avec le nombre maximum de signalements atteint.
	 */
	public function testReportMaximumReached(): void
	{
		for ($i = 0; $i < 3; $i++)
		{
			$this->client->request('POST', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9/report', [
				'reason' => 'Inappropriate content ' . $i,
			]);

			$this->assertResponseIsSuccessful();
			$this->assertJson($this->client->getResponse()->getContent());
		}

		$this->client->request('POST', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9/report', [
			'reason' => 'Inappropriate content',
		]);

		$this->assertResponseStatusCodeSame(Response::HTTP_TOO_MANY_REQUESTS);

		$content = $this->client->getResponse()->getContent();

		$this->assertJson($content);
		$this->assertJsonStringEqualsJsonString('{"code":429,"message":"The maximum number of reports for this link has been reached (3). It cannot be reported again."}', $content);
	}

	/**
	 * Test de création d'un signalement avec succès.
	 */
	public function testReportSuccessfully(): void
	{
		$this->client->request('POST', '/api/v1/link/0196cb17-b0f8-7e9c-b381-ef17aa05f3d9/report', [
			'reason' => 'Inappropriate content',
		]);

		$this->assertResponseIsSuccessful();
		$this->assertJson($this->client->getResponse()->getContent());
	}
}