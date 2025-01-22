//
// Fonctions utilitaires pour manipuler les dates.
//

"use client";

export const formatDate = ( date: Date, locale: string ) => new Intl.DateTimeFormat( locale, {
	year: "numeric",
	month: "long",
	day: "numeric",
	hour: "2-digit",
	minute: "2-digit",
	second: "2-digit"
} ).format( date );