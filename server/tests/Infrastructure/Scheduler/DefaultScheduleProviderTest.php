<?php

namespace App\Tests\Infrastructure\Scheduler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use App\Infrastructure\Scheduler\DefaultScheduleProvider;

/**
 * Tests of DefaultScheduleProvider.
 */
final class DefaultScheduleProviderTest extends TestCase
{
    public function testGetSchedule(): void
    {
        $cache = new ArrayAdapter();
        $schedule = (new DefaultScheduleProvider($cache))->getSchedule();

        self::assertSame($cache, $schedule->getState());
        self::assertTrue($schedule->shouldProcessOnlyLastMissedRun());
    }
}
