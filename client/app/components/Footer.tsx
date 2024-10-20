//
// Composant du pied de page du site.
//

"use client";

import { Link } from "@nextui-org/react";

export default function Footer()
{
	// Affichage du rendu HTML du composant.
	return (
		<footer className="container mx-auto mt-auto max-w-[1440px] p-4 !pt-0 md:p-8">
			<p className="text-default-400 text-sm">
				© {new Date().getFullYear()} Raven Url Shortener. Tous droits
				réservés.
			</p>

			<small className="text-default-400 text-sm">
				Ce site est protégé par reCAPTCHA sur lequel s&lsquo;appliquent
				les{" "}
				<Link
					href="https://policies.google.com/privacy"
					className="text-warning text-sm"
					isExternal
					showAnchorIcon
				>
					politiques de confidentialité
				</Link>{" "}
				et les{" "}
				<Link
					href="https://policies.google.com/terms"
					className="text-warning text-sm"
					isExternal
					showAnchorIcon
				>
					conditions d&lsquo;utilisation
				</Link>{" "}
				de Google.
			</small>
		</footer>
	);
}