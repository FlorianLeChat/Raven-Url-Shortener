//
// Action de récupération des détails d'un raccourci auprès du back-end PHP.
//

"use server";

import { logger } from "@/utilities/pino";
import { getTranslations } from "next-intl/server";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";

// Type de la réponse HTTP en provenance du back-end PHP.
type GetLinkDetailsResponse = LinkProperties | ErrorProperties;

export async function getLinkDetails( id?: string )
{
	// Vérification de la validité de l'identifiant unique du raccourci.
	const messages = await getTranslations();

	if ( !id )
	{
		return {
			state: false,
			message: messages( "errors.link.missing_or_invalid" )
		};
	}

	try
	{
		// Envoi de la requête HTTP de récupération des informations du raccourci.
		const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/link/${ id }`, {
			cache: "force-cache"
		} );
		const json = ( await response.json() ) as GetLinkDetailsResponse;

		logger.info(
			{ source: __dirname, json },
			"Short link fetching details response."
		);

		if ( response.ok && "id" in json )
		{
			// Tout s'est bien passé.
			return {
				state: true,
				data: json
			};
		}

		// En cas d'erreur lors de la récupération des informations.
		return {
			state: false,
			message: messages( "errors.generic_unknown" )
		};
	}
	catch ( error )
	{
		// En cas d'erreur lors de la récupération des informations
		//  ou lors de la conversion de la réponse en JSON.
		logger.error(
			{ source: __dirname, error },
			"An error occurred while fetching the short link details."
		);

		return {
			state: false,
			message: messages( "errors.link.fetch_failed" )
		};
	}
}