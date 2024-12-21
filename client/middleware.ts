//
// Mécanisme de routage pour les pages de l'application.
//
import { NextRequest } from "next/server";
import createIntlMiddleware from "next-intl/middleware";

import { getLanguages } from "./utilities/i18n";

export default async function middleware( request: NextRequest )
{
	// On créé le mécanisme de gestion des langues et traductions.
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