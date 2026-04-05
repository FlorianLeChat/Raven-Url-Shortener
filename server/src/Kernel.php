<?php

namespace App;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public const LOG_FUNCTION = '> file %s - namespace %s - function %s - line %s';

    public function boot(): void
    {
        parent::boot();

        date_default_timezone_set(getenv('TZ') ?: 'America/New_York');
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/logs';
    }
}
