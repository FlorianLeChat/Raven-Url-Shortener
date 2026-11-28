<?php

namespace App\Tests\Domain\Service;

use Psr\Log\NullLogger;
use App\Domain\Entity\Report;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\ReviewReportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use App\Infrastructure\Mailer\ReportReviewMailer;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Tests of ReviewReportService with a request AdminApiKeyListener did not recognize.
 */
final class ReviewReportServiceTest extends TestCase
{
    public function testReviewWithoutAdminApiKey(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('flush');

        $service = new ReviewReportService(
            new NullLogger(),
            new ReportReviewMailer(false, new NullLogger(), $this->createStub(MailerInterface::class), $translator),
            $translator,
            $entityManager
        );

        $request = Request::create('/v1/report', 'PATCH', ['status' => 'accepted']);
        $request->headers->set('Authorization', 'Bearer wrong-key');

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('invalid');

        $service->reviewReport($request, new Report());
    }
}
