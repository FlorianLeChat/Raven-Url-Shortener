//
// Action de récupération des détails d'un raccourci auprès du back-end PHP.
//

"use server";

import { logger } from "@/utilities/pino";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";

export async function getLinkDetails( id?: string )
{
	// Vérification de la validité de l'identifiant unique du raccourci.
	if ( !id )
	{
		return {
			state: false,
			message: "L'identifiant du raccourci est manquant ou invalide."
		};
	}

	// Envoi de la requête HTTP de récupération des informations du raccourci.
	const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/link/${ id }` );

	try
	{
		const json = ( await response.json() ) as LinkProperties | ErrorProperties;

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
			message: "message" in json
				? json.message
				: "Une erreur est survenue lors de la récupération des informations du raccourci."
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
			message: "Une erreur est survenue lors de la récupération des informations du raccourci."
		};
	}
}