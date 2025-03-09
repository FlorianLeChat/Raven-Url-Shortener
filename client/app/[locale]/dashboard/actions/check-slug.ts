//
// Action de vérification de la disponibilité du slug personnalisé auprès du back-end PHP.
//

"use server";

import { logger } from "@/utilities/pino";
import { getTranslations } from "next-intl/server";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";

// Type de la réponse HTTP en provenance du back-end PHP.
type SlugCheckResponse = ErrorProperties | {
	available: boolean;
};

export async function checkSlug( slug?: string )
{
	// Vérification de la validité du slug personnalisé.
	const messages = await getTranslations();

	if ( !slug )
	{
		return {
			state: false,
			message: messages( "errors.slug.missing_or_invalid" )
		};
	}

	// Envoi de la requête HTTP de récupération des informations du raccourci.
	const formData = new FormData();
	formData.append( "slug", slug );

	try
	{
		const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/slug`, {
			body: formData,
			method: "POST"
		} );
		const json = ( await response.json() ) as SlugCheckResponse;

		logger.info(
			{ source: __dirname, json },
			"Slug availability checking response."
		);

		if ( response.ok && "available" in json )
		{
			// Tout s'est bien passé.
			return {
				state: true,
				available: json.available
			};
		}

		// En cas d'erreur lors de la récupération de la disponibilité du slug.
		if ( "errors" in json && json.errors )
		{
			const keys = Object.keys( json.errors );
			const error = json.errors[ keys[ 0 ] ];

			return {
				state: false,
				message: messages( `errors.${ error[ 0 ].code }` )
			};
		}

		return {
			state: false,
			message: messages( "errors.slug.check_failed" )
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
			message: messages( "errors.slug.check_failed" )
		};
	}
}