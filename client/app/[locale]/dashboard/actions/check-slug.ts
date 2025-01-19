//
// Action de vérification de la disponibilité du slug personnalisé auprès du back-end PHP.
//

"use server";

import { logger } from "@/utilities/pino";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";

export async function checkSlug( slug?: string )
{
	// Vérification de la validité du slug personnalisé.
	if ( !slug )
	{
		return {
			state: false,
			message: "Le slug personnalisé est manquant ou invalide."
		};
	}

	// Envoi de la requête HTTP de récupération des informations du raccourci.
	const formData = new FormData();
	formData.append( "slug", slug );

	const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/slug`, {
		body: formData,
		method: "POST"
	} );

	try
	{
		const json = ( await response.json() ) as
			| { available: boolean }
			| ErrorProperties;

		logger.info(
			{ source: __dirname, json },
			"Slug availability checking response."
		);

		if ( response.ok && "available" in json )
		{
			// Tout s'est bien passé.
			return {
				state: true,
				isAvailable: json.available
			};
		}

		// En cas d'erreur lors de la récupération de la disponibilité du slug.
		return {
			state: false,
			message: "message" in json
				? json.message
				: "Une erreur est survenue lors de la récupération de la disponibilité du slug."
		};
	}
	catch ( error )
	{
		// En cas d'erreur lors de la récupération de la disponibilité
		//  du slug ou lors de la conversion de la réponse en JSON.
		logger.error(
			{ source: __dirname, error },
			"An error occurred while fetching the slug availability."
		);

		return {
			state: false,
			message: "Une erreur est survenue lors de la récupération de la disponibilité du slug."
		};
	}
}