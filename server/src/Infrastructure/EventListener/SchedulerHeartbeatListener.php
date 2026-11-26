<?php

namespace App\Infrastructure\EventListener;

use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Reports the activity of the task scheduler: the touched file is watched
 *  by the health check of the Docker container.
 */
final readonly class SchedulerHeartbeatListener implements EventSubscriberInterface
{
    private string $file;

    /**
     * @param string|null $file File touched on each loop, in the temporary directory by default.
     */
    public function __construct(?string $file = null)
    {
        $this->file = $file ?? sys_get_temp_dir() . '/scheduler.heartbeat';
    }

    /**
     * Updates the modification date of the heartbeat file.
     */
    public function onWorkerRunning(): void
    {
        touch($this->file);
    }

    /**
     * Listens to every loop of the Messenger worker running the scheduler.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }
}
