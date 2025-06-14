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
		$email = !empty($email) ? trim($email) : null;
		$reason = trim($reason);

		$report = new Report();
		$report->setLink($link);
		$report->setEmail($email);
		$report->setReason($reason);

		return $report;
	}
}