<?php

namespace App\Infrastructure\Security;

/**
 * Classe utilitaire pour la gestion des domaines de confiance.
 */
final class TrustedDomains
{
	/**
	 * Liste des domaines de confiance.
	 */
	public const DOMAINS = [
		'dailymotion.com',
		'youtube.com',
		'google.com',
		'ecosia.org',
		'x.com',
		'facebook.com',
		'twitter.com',
		'twitch.tv',
		'vimeo.com',
		'spotify.com',
		'tiktok.com',
		'soundcloud.com',
		'linkedin.com',
		'instagram.com',
		'whatsapp.com',
		'microsoft.com',
		'apple.com',
		'amazon.com',
		'netflix.com',
		'paypal.com',
		'reddit.com',
		'github.com',
		'stackoverflow.com',
		'wikipedia.org',
		'bing.com',
		'yahoo.com',
		'dropbox.com',
		'zoom.us',
		'slack.com',
		'adobe.com',
		'icloud.com',
		'pinterest.com',
		'ebay.com',
		'shopify.com',
		'airbnb.com',
		'uber.com',
		'lyft.com',
		'mozilla.org',
		'amd.com',
		'bbc.com',
		'cnn.com',
	];

	/**
	 * Vérifie si un domaine ou un sous-domaine est de confiance.
	 */
	public static function isTrusted(string $url): bool
	{
		$host = parse_url($url, PHP_URL_HOST);

		if (empty($host))
		{
			return false;
		}

		$host = preg_replace('/^www\./i', '', $host);

		foreach (self::DOMAINS as $domain)
		{
			if ($host === $domain || str_ends_with($host, '.' . $domain))
			{
				return true;
			}
		}

		return false;
	}
}