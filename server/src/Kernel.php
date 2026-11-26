<?php

namespace App;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

/**
 * Symfony kernel of the API.
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public const LOG_FUNCTION = '> file %s - namespace %s - function %s - line %s';

    /**
     * Boots the kernel, then sets the default time zone of PHP from the `TZ` environment variable.
     * `getenv()` only reads the real environment of the process, not the `.env` file loaded by Symfony:
     *  the Docker services therefore receive `TZ` from Compose.
     */
    public function boot(): void
    {
        parent::boot();

        date_default_timezone_set(getenv('TZ') ?: 'America/New_York');
    }

    /**
     * Writes the logs in `logs/` at the root of the project, mounted as a volume by the Docker services.
     */
    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/logs';
    }
}
