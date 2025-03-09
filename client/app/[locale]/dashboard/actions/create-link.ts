//
// Action de création d'un nouveau raccourci auprès du back-end PHP.
//

"use server";

import { logger } from "@/utilities/pino";
import { getTranslations } from "next-intl/server";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";

// Type de la réponse HTTP en provenance du back-end PHP.
type CreateLinkResponse = LinkProperties | ErrorProperties;

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
	const messages = await getTranslations();

	try
	{
		const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/link`, {
			body: data,
			method: "POST"
		} );
		const json = ( await response.json() ) as CreateLinkResponse;

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

		// En cas d'erreur lors de la création du raccourci.
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
			message: messages( "errors.generic_unknown" )
		};
	}
	catch ( error )
	{
		// En cas d'erreur lors de la création du raccourci,
		//  ou lors de la conversion de la réponse en JSON.
		logger.error(
			{ source: __dirname, error },
			"An error occurred while creating the short link."
		);

		return {
			state: false,
			message: messages( "errors.generic_unknown" )
		};
	}
}