<?php

namespace App\Tests\Action;

use App\Tests\TestHelperTrait;
use App\Domain\Entity\AccessKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests of `POST /v1/access-key`, `GET /v1/access-key` and `DELETE /v1/access-key/{id}`,
 *  and of the access keys sent in the `X-Api-Key` header.
 */
final class AccessKeyActionTest extends WebTestCase
{
    use TestHelperTrait;

    private const ADMIN_HEADERS = ['HTTP_Authorization' => 'Bearer test_admin_api_key'];

    private const FULL_KEY = 'ysvT1q67SCh7h+jAqC/gbrISbIMsuCBONZL4sK8/Urs=';
    private const EXPIRED_KEY = 'hbUS0gq8vnS1We2OZXeEntKRHcIfyTxV6daeVYLg2IA=';
    private const REVOKED_KEY = '8Tn5q3RP4gg//sM0VrmKpAjDnacq0VfYxXWvB1db81I=';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        self::loadFixtures($this->client->getKernel()->getProjectDir());
    }

    /**
     * Sends a JSON request as an administrator.
     *
     * @param array<string, mixed> $body
     */
    private function requestAsAdmin(string $method, string $uri, array $body = []): void
    {
        $this->client->request($method, $uri, server: self::ADMIN_HEADERS, content: json_encode(
            $body,
            JSON_THROW_ON_ERROR
        ));
    }

    /**
     * Decodes the JSON body of the last response.
     *
     * @return array<mixed>
     */
    private function getResponseBody(): array
    {
        $content = (string) $this->client->getResponse()->getContent();

        /** @var array<mixed> */
        return json_decode($content, true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Creates a link with the access key in the `X-Api-Key` header, and answers the response body.
     *
     * @return array<mixed>
     */
    private function createLinkWithAccessKey(string $accessKey): array
    {
        $this->client->request('POST', '/v1/link', ['url' => 'https://raven.example.com/'], server: [
            'HTTP_X_API_KEY' => $accessKey
        ]);

        return $this->getResponseBody();
    }

    public function testCreateAccessKey(): void
    {
        $this->requestAsAdmin('POST', '/v1/access-key', [
            'name' => '  Partner  ',
            'capabilities' => ['trusted_links', 'captcha_bypass'],
            'allowedOrigins' => [' https://Partner.example.com:8443/ '],
            'expiration' => '+1 month'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $accessKey = $this->getResponseBody();

        self::assertSame('Partner', $accessKey['name']);
        self::assertSame(44, strlen($accessKey['key']));
        self::assertSame(['trusted_links', 'captcha_bypass'], $accessKey['capabilities']);
        self::assertSame(['https://partner.example.com:8443'], $accessKey['allowedOrigins']);
        self::assertTrue($accessKey['active']);
        self::assertNotNull($accessKey['expiresAt']);
        self::assertNull($accessKey['revokedAt']);

        $link = $this->createLinkWithAccessKey($accessKey['key']);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertTrue($link['trusted']);
    }

    public function testCreateAccessKeyWithInvalidData(): void
    {
        $this->requestAsAdmin('POST', '/v1/access-key', ['name' => 'Partner', 'expiration' => 'not a date']);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertStringContainsString('INVALID_DATE_ERROR', (string) $this->client->getResponse()->getContent());

        $this->requestAsAdmin('POST', '/v1/access-key', [
            'name' => ' ',
            'capabilities' => ['admin', 42],
            'allowedOrigins' => ['https://partner.example.com/path'],
            'expiration' => '+2 years'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $violations = $this->getResponseBody();

        self::assertSame('IS_BLANK_ERROR', $violations['errors']['name'][0]['code']);
        self::assertSame('NO_SUCH_CHOICE_ERROR', $violations['errors']['capabilities'][0]['code']);
        self::assertSame('REGEX_FAILED_ERROR', $violations['errors']['allowedOrigins[0]'][0]['code']);
        self::assertSame('NOT_IN_RANGE_ERROR', $violations['errors']['expiresAt'][0]['code']);

        $this->requestAsAdmin('POST', '/v1/access-key', ['name' => 'Partner']);

        self::assertStringContainsString('TOO_FEW_ERROR', (string) $this->client->getResponse()->getContent());
    }

    public function testAccessKeyNeverOpensAdministratorRoutes(): void
    {
        $this->client->request('GET', '/v1/access-key', server: ['HTTP_X_API_KEY' => self::FULL_KEY]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $this->client->request('DELETE', '/v1/access-key/01a2239e-23a4-771b-b71b-c2981e342853');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $this->client->request('GET', '/v1/access-key', server: [
            'HTTP_X_API_KEY' => self::FULL_KEY,
            'HTTP_Authorization' => 'Bearer ' . self::FULL_KEY
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testListAccessKeys(): void
    {
        $this->requestAsAdmin('GET', '/v1/access-key');

        self::assertResponseIsSuccessful();

        $accessKeys = $this->getResponseBody();
        $activeKeys = array_column($accessKeys, 'active', 'name');

        self::assertSame(['full' => true, 'trusted' => true, 'expired' => false, 'revoked' => false], $activeKeys);
        self::assertSame([null], array_unique(array_column($accessKeys, 'key')));
    }

    public function testRevokeAccessKey(): void
    {
        $entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $fullKey = $entityManager->getRepository(AccessKey::class)->findOneBy(['name' => 'full']);
        $uri = '/v1/access-key/' . $fullKey?->getId();

        $this->createLinkWithAccessKey(self::FULL_KEY);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->requestAsAdmin('DELETE', $uri);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('"active":false', (string) $this->client->getResponse()->getContent());

        $this->requestAsAdmin('DELETE', $uri);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        self::assertJsonStringEqualsJsonString(
            '{"code":409,"message":"This access key has already been revoked."}',
            (string) $this->client->getResponse()->getContent()
        );

        $this->createLinkWithAccessKey(self::FULL_KEY);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRefusedAccessKeys(): void
    {
        foreach ([self::EXPIRED_KEY, self::REVOKED_KEY, 'unknown-key'] as $accessKey) {
            $this->createLinkWithAccessKey($accessKey);

            self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }
}
