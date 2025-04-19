//
// Action de création d'un nouveau raccourci auprès du back-end PHP.
//

"use server";

import { logger } from "@/utilities/pino";
import { getTranslations } from "next-intl/server";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";

type CreateLinkResponse = LinkProperties | ErrorProperties;

export async function createLink( data: FormData )
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
		const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/v1/link`, {
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
			return {
				state: true,
				data: json
			};
		}

		return {
			state: false,
			message: messages( "errors.link.creation_failed" )
		};
	}
	catch ( error )
	{
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