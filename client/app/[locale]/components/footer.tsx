//
// Composant du pied de page du site.
//

"use client";

import { useTranslations } from "next-intl";

export default function Footer()
{
	// Déclaration des variables d'état.
	const messages = useTranslations( "footer" );

	// Affichage du rendu HTML du composant.
	return (
		<footer className="container mx-auto mt-auto max-w-[1440px] p-4 !pt-0 md:p-8">
			<p className="text-sm text-default-500">
				© {new Date().getFullYear()} Raven Url Shortener.{" "}
				{messages( "rights_reserved" )}.
			</p>
		</footer>
	);
}