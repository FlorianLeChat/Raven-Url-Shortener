<?php

namespace App\Domain\Enum;

/**
 * Check an access key relaxes for the requests sending it (see AccessKeyListener).
 * No capability opens the administrator endpoints: they only accept the administrator API key.
 */
enum AccessKeyCapability: string
{
    case PrivateApi = 'private_api';
    case RateLimitBypass = 'rate_limit_bypass';
    case CaptchaBypass = 'captcha_bypass';
    case TrustedLinks = 'trusted_links';

    /**
     * Values of every capability, as the `Choice` constraint of AccessKey lists them.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
