//
// Récupération de certaines informations du serveur.
//

import { headers } from "next/headers";

export const getTimeZoneName = () =>
{
	// Nom du fuseau horaire actuel.
	const options = Intl.DateTimeFormat().resolvedOptions();

	return process.env.TZ ?? options.timeZone;
};

export const getTimeZoneOffset = () =>
{
	// Décalage en minutes (exemple : -60 pour UTC+1) et conversion en heures.
	const offsetMinutes = new Date().getTimezoneOffset();
	const offsetHours = -offsetMinutes / 60;

	return `UTC${ offsetHours >= 0 ? "+" : "" }${ offsetHours }`;
};

export const getDomain = async () =>
{
	// Adresse de base du domaine.
	const requestHeaders = await headers();

	return `${ requestHeaders.get( "x-forwarded-proto" ) ?? "http" }://${ requestHeaders.get( "host" ) }/`;
};