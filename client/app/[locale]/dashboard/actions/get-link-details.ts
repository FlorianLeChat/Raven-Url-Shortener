//
// Action de récupération des détails d'un raccourci auprès du back-end PHP.
//

"use server";

import { getTranslations } from "next-intl/server";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";

type GetLinkDetailsResponse = LinkProperties | ErrorProperties;

export async function getLinkDetails( id?: string )
{
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
		const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/v1/link/${ id }`, {
			cache: "force-cache"
		} );

		const json = ( await response.json() ) as GetLinkDetailsResponse;

		console.log( "Short link fetching details response." );
		console.table( json );

		if ( response.ok && "id" in json )
		{
			return {
				state: true,
				data: json
			};
		}

		return {
			state: false,
			message: messages( "errors.link.fetch_failed" )
		};
	}
	catch ( error )
	{
		console.log( "An error occurred while fetching the short link details." );
		console.table( error );

		return {
			state: false,
			message: messages( "errors.generic_unknown" )
		};
	}
}