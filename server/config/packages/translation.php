<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/**
 * Paramétrage pour le composant Translation de Symfony.
 * @see https://symfony.com/doc/current/translation.html
 */
return static function (FrameworkConfig $framework): void
{
	$framework
		->defaultLocale('en')
		->enabledLocales(['en', 'fr'])
		->setLocaleFromAcceptLanguage(true)
		->setContentLanguageFromLocale(true)
		->translator()
			->defaultPath('%kernel.project_dir%/locales');
};