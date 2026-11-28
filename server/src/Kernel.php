<?php

namespace App;

use LogicException;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

/**
 * Symfony kernel of the API.
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Trace written at the start of each method, at the debug level: production only keeps info and above.
     */
    public const LOG_FUNCTION = '> file %s - namespace %s - function %s - line %s';

    /**
     * Boots the kernel, checks the CAPTCHA settings, then sets the default time zone of PHP
     *  from the `TZ` environment variable.
     * `getenv()` only reads the real environment of the process, not the `.env` file loaded by Symfony:
     *  the Docker services therefore receive `TZ` from Compose.
     * @throws LogicException When a private API has no `CAPTCHA_HMAC`.
     */
    public function boot(): void
    {
        parent::boot();

        $this->checkCaptchaKey();

        date_default_timezone_set(getenv('TZ') ?: 'America/New_York');
    }

    /**
     * Refuses to start a private API without `CAPTCHA_HMAC`: its CAPTCHA challenges would be signed
     *  with an empty key, which anyone can reproduce.
     * @throws LogicException When `PRIVATE_API` is set and `CAPTCHA_HMAC` is empty.
     */
    private function checkCaptchaKey(): void
    {
        $isPrivateApi = $this->getContainer()->getParameter('app.private_api') === true;
        $isMissingKey = $this->getContainer()->getParameter('app.captcha_hmac') === '';

        if ($isPrivateApi && $isMissingKey) {
            throw new LogicException('CAPTCHA_HMAC must be set when the API is private.');
        }
    }

    /**
     * Writes the logs in `logs/` at the root of the project, mounted as a volume by the Docker services.
     */
    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/logs';
    }
}
