<?php

namespace App\Infrastructure\Scheduler;

use Symfony\Component\Scheduler\Schedule;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

/**
 * Schedule of the periodic tasks declared with `#[AsPeriodicTask]`.
 */
#[AsSchedule]
final readonly class DefaultScheduleProvider implements ScheduleProviderInterface
{
    public function __construct(private CacheInterface $cache)
    {
    }

    /**
     * The state is kept in the cache so that the last run missed during a restart of the service is caught up.
     */
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true);
    }
}
