//
// Action de signalement d'un raccourci auprès du back-end PHP.
//

"use server";

import { logger } from "@/utilities/pino";
import { getTranslations } from "next-intl/server";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";

export async function reportLink( data: FormData )
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

	// Envoi de la requête HTTP de signalement d'un raccourci.
	const messages = await getTranslations();

	try
	{
		const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/link/report`, {
			body: data,
			method: "POST"
		} );

		logger.info(
			{ source: __dirname, data: Array.from( data.entries() ) },
			"Short link reporting response."
		);

		if ( response.ok )
		{
			// Tout s'est bien passé.
			return { state: true };
		}

		// En cas d'erreur lors du signalement du raccourci.
		const json = ( await response.json() ) as ErrorProperties;

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
			message: messages( "errors.report.send_failed" )
		};
	}
	catch ( error )
	{
		// En cas d'erreur lors du signalement du raccourci,
		//  ou lors de la conversion de la réponse en JSON.
		logger.error(
			{ source: __dirname, error },
			"An error occurred while reporting the short link."
		);

		return {
			state: false,
			message: messages( "errors.report.send_failed" )
		};
	}
}