<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Link;
use App\Domain\Entity\Report;

/**
 * Fabrique pour les signalements de liens raccourcis.
 */
final class ReportFactory
{
	/**
	 * Création d'un signalement.
	 */
	public static function create(Link $link, string $reason, ?string $email = null): Report
	{
		$report = new Report();
		$report->setLink($link);
		$report->setEmail(!empty($email) ? trim($email) : null);
		$report->setReason(trim($reason));

		return $report;
	}
}