//
// Action de vérification de la disponibilité du slug personnalisé auprès du back-end PHP.
//

"use server";

import { logger } from "@/utilities/pino";
import { getTranslations } from "next-intl/server";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";

type SlugCheckResponse = ErrorProperties | {
	available: boolean;
};

export async function checkSlug( slug?: string )
{
	const messages = await getTranslations();

	if ( !slug )
	{
		return {
			state: false,
			message: messages( "errors.slug.missing_or_invalid" )
		};
	}

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
			return {
				state: true,
				available: json.available
			};
		}

		return {
			state: false,
			message: messages( "errors.slug.check_failed" )
		};
	}
	catch ( error )
	{
		logger.error(
			{ source: __dirname, error },
			"An error occurred while fetching the slug availability."
		);

		return {
			state: false,
			message: messages( "errors.generic_unknown" )
		};
	}
}