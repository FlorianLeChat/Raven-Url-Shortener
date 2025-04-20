//
// Action de signalement d'un raccourci auprès du back-end PHP.
//

"use server";

import { logger } from "@/utilities/pino";
import { getTranslations } from "next-intl/server";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";

export async function reportLink( data: FormData )
{
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

	const messages = await getTranslations();

	try
	{
		const linkId = data.get( "id" );
		const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/v1/link/${ linkId }/report`, {
			body: data,
			method: "POST"
		} );

		const json = ( await response.json() ) as ErrorProperties;

		logger.info(
			{ source: __dirname, json },
			"Short link reporting response."
		);

		if ( response.ok )
		{
			return { state: true };
		}

		return {
			state: false,
			message: messages( "errors.report.send_failed" )
		};
	}
	catch ( error )
	{
		logger.error(
			{ source: __dirname, error },
			"An error occurred while reporting the short link."
		);

		return {
			state: false,
			message: messages( "errors.generic_unknown" )
		};
	}
}