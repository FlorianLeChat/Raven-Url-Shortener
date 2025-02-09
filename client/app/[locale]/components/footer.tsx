//
// Composant du pied de page du site.
//

"use client";

import { Link } from "@heroui/react";
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

			{/* Avertissement de Google reCAPTCHA */}
			{process.env.NEXT_PUBLIC_RECAPTCHA_ENABLED === "true" && (
				<small className="text-sm text-default-500">
					{messages.rich( "recaptcha_protected", {
						a1: ( chunks ) => (
							<Link
								href="https://policies.google.com/privacy"
								className="text-sm text-primary"
								isExternal
								showAnchorIcon
							>
								{chunks}
							</Link>
						),
						a2: ( chunks ) => (
							<Link
								href="https://policies.google.com/terms"
								className="text-sm text-primary"
								isExternal
								showAnchorIcon
							>
								{chunks}
							</Link>
						)
					} )}
				</small>
			)}
		</footer>
	);
}