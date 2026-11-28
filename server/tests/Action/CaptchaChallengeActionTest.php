<?php

namespace App\Tests\Action;

use Psr\Log\LoggerInterface;
use App\Action\CaptchaChallengeAction;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\Store\InMemoryStore;
use App\Infrastructure\Security\CaptchaVerifier;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Tests of `GET /v1/captcha`.
 */
final class CaptchaChallengeActionTest extends WebTestCase
{
    public function testChallengeWithPublicApi(): void
    {
        $client = self::createClient();
        $client->request('GET', '/v1/captcha');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testChallengeWithPrivateApi(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $verifier = new CaptchaVerifier(true, 'captcha-hmac-key', new LockFactory(new InMemoryStore()), $translator);
        $action = new CaptchaChallengeAction($this->createStub(LoggerInterface::class), $verifier);

        $response = $action->getChallenge();
        $content = (string) $response->getContent();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        self::assertJson($content);
        self::assertStringContainsString('"algorithm":"SHA-256"', $content);
        self::assertStringContainsString('"maxnumber":100000', $content);
    }
}
