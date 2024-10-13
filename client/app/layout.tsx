//
// Structure HTML générale des pages du site.
//  Source : https://nextjs.org/docs/app/building-your-application/routing/pages-and-layouts#root-layout-required
//

// Importation du normalisateur TypeScript.
import "@total-typescript/ts-reset";

// Importation des dépendances.
import "./layout.css";
import { Inter } from "next/font/google";
import type { ReactNode } from "react";

// Importation des fonctions utilitaires.s
import { NextUIProvider } from "../utilities/next-ui";

// Création des polices de caractères.
const inter = Inter( {
	subsets: [ "latin" ],
	display: "swap"
} );

export default function Layout( { children }: { children: ReactNode } )
{
	return (
		<html lang="fr" className={`text-foreground dark ${ inter.className }`}>
			<body>
				{/* Vidéo en arrière-plan */}
				<video
					loop
					muted
					autoPlay
					className="fixed -z-10 hidden size-full object-none opacity-10 dark:block"
				>
					<source
						src={`${ process.env.__NEXT_ROUTER_BASEPATH }/assets/videos/background.mp4`}
						type="video/mp4"
					/>
				</video>

				{/* Utilisation de NextUI */}
				<NextUIProvider className="flex min-h-screen flex-col">
					{/* Composant enfant */}
					{children}
				</NextUIProvider>
			</body>
		</html>
	);
}