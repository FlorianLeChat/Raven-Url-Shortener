<?php

namespace App;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;

// Définition du format des journaux d'événements de l'application.
const LOG_FUNCTION = '> file %s - namespace %s - function %s - line %s';

final class Kernel extends BaseKernel
{
	use MicroKernelTrait;

	public function boot(): void
	{
		// Appel de la méthode provenant de la classe parente.
		parent::boot();

		// Définition du fuseau horaire pour l'ensemble de l'application.
		date_default_timezone_set(getenv('TZ') ?: 'America/New_York');
	}

	public function getLogDir(): string
	{
		// Modification de l'emplacement des journaux d'événements.
		return $this->getProjectDir() . '/logs';
	}
}