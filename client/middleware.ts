//
// Mécanisme de routage pour les pages de l'application.
//
import createIntlMiddleware from "next-intl/middleware";
import { NextRequest, NextResponse } from "next/server";

import { getLanguages } from "./utilities/i18n";
import { getLinkDetails } from "./app/[locale]/dashboard/actions/get-link-details";

export default async function middleware( request: NextRequest )
{
	// Vérification à l'accès d'un lien raccourci.
	const uuidPattern = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/;
	const slugPattern = /^[a-zA-Z0-9-]+$/;

	const pathName = request.nextUrl.pathname.slice( 1 );
	const isValidUuid = uuidPattern.test( pathName );
	const isMaybeSlug = pathName.length <= 50 && slugPattern.test( pathName );

	if ( isValidUuid || isMaybeSlug )
	{
		// Le lien contient un UUID ou ressemble à un slug.
		const details = await getLinkDetails( pathName );

		if ( details.state && details.data )
		{
			// Redirection vers la page de redirection.
			return NextResponse.redirect( new URL( `/go/${ details.data.id }`, request.nextUrl ) );
		}
	}

	// Création du mécanisme de gestion des langues et traductions.
	//  Source : https://next-intl-docs.vercel.app/docs/getting-started/app-router-server-components
	const handleI18nRouting = createIntlMiddleware( {
		locales: getLanguages(),
		localePrefix: "never",
		defaultLocale: "en"
	} );

	return handleI18nRouting( request );
}

export const config = {
	matcher: [
		"/",
		"/((?!assets|locales|_next|_vercel|sitemap.xml|manifest.webmanifest).*)"
	]
};