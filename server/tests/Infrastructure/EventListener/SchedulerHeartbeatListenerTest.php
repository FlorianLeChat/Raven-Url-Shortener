<?php

namespace App\Tests\Infrastructure\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use App\Infrastructure\EventListener\SchedulerHeartbeatListener;

final class SchedulerHeartbeatListenerTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        self::assertSame(
            [WorkerRunningEvent::class => 'onWorkerRunning'],
            SchedulerHeartbeatListener::getSubscribedEvents()
        );
    }

    public function testWorkerRunning(): void
    {
        $file = sys_get_temp_dir() . '/scheduler-heartbeat-test';

        if (is_file($file)) {
            unlink($file);
        }

        (new SchedulerHeartbeatListener($file))->onWorkerRunning();

        self::assertFileExists($file);
        self::assertGreaterThanOrEqual(time() - 5, filemtime($file));

        unlink($file);
    }

    public function testDefaultFile(): void
    {
        $file = sys_get_temp_dir() . '/scheduler.heartbeat';

        (new SchedulerHeartbeatListener())->onWorkerRunning();

        self::assertFileExists($file);
    }
}
