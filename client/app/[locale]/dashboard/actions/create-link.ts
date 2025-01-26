//
// Action de création d'un nouveau raccourci auprès du back-end PHP.
//

"use server";

import { logger } from "@/utilities/pino";
import { captureException } from "@sentry/nextjs";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";

export async function createLink( data: FormData )
{
	// Suppression de toutes les entrées vides.
	data.forEach( ( value, key ) =>
	{
		if ( !value )
		{
			data.delete( key );
		}
	} );

	logger.debug(
		{ source: __dirname, data: Array.from( data.entries() ) },
		"Form data before sending to the server."
	);

	// Envoi de la requête HTTP de création d'un nouveau raccourci.
	const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/link`, {
		body: data,
		method: "POST"
	} );

	try
	{
		const json = ( await response.json() ) as LinkProperties | ErrorProperties;

		logger.info(
			{ source: __dirname, json },
			"Short link creation response."
		);

		if ( response.ok && "id" in json )
		{
			// Tout s'est bien passé.
			return {
				state: true,
				data: json
			};
		}

		// En cas d'erreur lors de la création du raccourci,
		return {
			state: false,
			message: "message" in json
				? json.message
				: "Une erreur est survenue lors de la création du raccourci."
		};
	}
	catch ( error )
	{
		// En cas d'erreur lors de la création du raccourci,
		//  ou lors de la conversion de la réponse en JSON.
		captureException( error );

		logger.error(
			{ source: __dirname, error },
			"An error occurred while creating the short link."
		);

		return {
			state: false,
			message: "Une erreur est survenue lors de la création du raccourci."
		};
	}
}