<?php

namespace App\Infrastructure\EventListener;

use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Signale l'activité du planificateur de tâches : le fichier mis à jour
 *  est surveillé par la vérification de santé du conteneur Docker.
 */
final readonly class SchedulerHeartbeatListener implements EventSubscriberInterface
{
    private string $file;

    public function __construct(?string $file = null)
    {
        $this->file = $file ?? sys_get_temp_dir() . '/scheduler.heartbeat';
    }

    public function onWorkerRunning(): void
    {
        touch($this->file);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }
}
