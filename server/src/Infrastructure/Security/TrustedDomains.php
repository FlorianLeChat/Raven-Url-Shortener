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
		'gitlab.com',
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
		'openai.com',
		'medium.com',
		'notion.so',
		'quora.com',
		'coursera.org',
		'edx.org',
		'udemy.com',
		'kickstarter.com',
		'deezer.com',
		'trello.com',
		'asana.com',
		'canva.com',
		'fiverr.com',
		'freelancer.com',
		'wetransfer.com',
		'tumblr.com',
		'telegram.org',
		'protonmail.com',
		'icloud.com',
		'ycombinator.com',
		'producthunt.com',
		'hackernews.com',
		'imdb.com',
		'rottentomatoes.com',
		'crunchbase.com',
		'indeed.com',
		'glassdoor.com',
		'salesforce.com',
		'zendesk.com',
		'figma.com',
		'behance.net'
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

		/** @var string $host */
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