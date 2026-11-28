<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Action\ServerIndexAction;
use App\Tests\Mock\DnsRecordResolver;
use App\Tests\Mock\HttpClientResponseFactory;
use App\Infrastructure\Security\CaptchaVerifier;
use App\Infrastructure\Mailer\ReportReviewMailer;
use App\Infrastructure\Command\UserReportSummary;
use App\Infrastructure\EventListener\OriginListener;
use App\Infrastructure\EventListener\LimiterListener;
use App\Infrastructure\EventListener\ExceptionListener;
use App\Infrastructure\EventListener\AdminApiKeyListener;
use Egulias\EmailValidator\Validation\DNSGetRecordWrapper;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * @see https://symfony.com/doc/current/service_container.html
 */
return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('app.private_api', '%env(bool:PRIVATE_API)%')
        ->set('app.captcha_hmac', '%env(string:CAPTCHA_HMAC)%');

    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $excludes = [
        '../src/Kernel.php',
        '../src/Domain/Entity',
        '../src/DependencyInjection',
    ];

    if ($container->env() === 'prod') {
        $excludes[] = '../src/Infrastructure/Fixture';
    }

    $services->load('App\\', '../src/')
        ->exclude($excludes);

    $services->set(UserReportSummary::class)
        ->bind('bool $isSmtpEnabled', '%env(bool:SMTP_ENABLED)%')
        ->bind('string $smtpUsername', '%env(string:SMTP_USERNAME)%')
        ->tag('console.command');

    $services->set(ReportReviewMailer::class)
        ->bind('bool $isSmtpEnabled', '%env(bool:SMTP_ENABLED)%')
        ->tag('kernel.event_listener', ['event' => 'kernel.terminate', 'method' => 'sendQueuedOutcomes']);

    $services->set(AdminApiKeyListener::class)
        ->bind('string $adminApiKey', '%env(string:ADMIN_API_KEY)%')
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest', 'priority' => 128]);

    $services->set(CaptchaVerifier::class)
        ->bind('bool $isPrivateApi', '%app.private_api%')
        ->bind('string $hmacKey', '%app.captcha_hmac%')
        ->arg('$lockFactory', service('lock.captcha.factory'));

    $services->set(ServerIndexAction::class)
        ->bind('string $version', '%env(string:VERSION)%');

    $services->set(ExceptionListener::class)
        ->tag('kernel.event_listener');

    $services->set(OriginListener::class)
        ->bind('bool $isPrivateApi', '%env(bool:PRIVATE_API)%')
        ->bind('array $allowedOrigins', '%env(csv:PRIVATE_API_ALLOWED_ORIGINS)%')
        ->bind('string $apiKey', '%env(string:PRIVATE_API_KEY)%')
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest', 'priority' => 24])
        ->tag('kernel.event_listener', ['event' => 'kernel.response', 'method' => 'onKernelResponse']);

    if ($container->env() === 'test') {
        $services->set(HttpClientResponseFactory::class);
        $services->set(DNSGetRecordWrapper::class, DnsRecordResolver::class);
    } else {
        $services->set(DNSGetRecordWrapper::class);

        // Shortened URLs are typed by users: checking them must never reach
        //  a service of the private network.
        $services->set(NoPrivateNetworkHttpClient::class)
            ->decorate('http_client')
            ->args([service('.inner')]);
    }

    if ($container->env() === 'prod') {
        $services->set(LimiterListener::class)
            ->bind('bool $isRateLimitEnabled', '%env(bool:RATE_LIMIT_ENABLED)%')
            ->bind('array $enabledLocales', '%kernel.enabled_locales%')
            ->tag('kernel.event_listener', ['priority' => 64]);
    }
};
