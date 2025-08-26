//
// Structure HTML générale des pages du site.
//  Source : https://nextjs.org/docs/app/building-your-application/routing/pages-and-layouts#root-layout-required
//

// Importation des feuilles de style CSS.
import "../layout.css";
import "vanilla-cookieconsent/dist/cookieconsent.css";

// Importation des dépendances.
import { Inter } from "next/font/google";
import { lazy, type ReactNode } from "react";
import { getMessages, setRequestLocale } from "next-intl/server";

// Importation des fonctions utilitaires.
import { getDomain,
	getTimeZoneName,
	getTimeZoneOffset } from "@/utilities/server";
import { getLanguages } from "@/utilities/i18n";
import { fetchMetadata } from "@/utilities/metadata";

// Importation des types.
import type { Viewport } from "next";

// Importation des composants.
const Footer = lazy( () => import( "@/components/global-footer" ) );
const Providers = lazy( () => import( "@/components/global-providers" ) );
const CookieConsent = lazy( () => import( "@/components/consent-cookie" ) );

// Déclaration des paramètres d'affichage.
export const viewport: Viewport = {
	viewportFit: "cover",
	themeColor: [
		{ media: "(prefers-color-scheme: light)", color: "#c2d0e0" },
		{ media: "(prefers-color-scheme: dark)", color: "#0072f5" }
	]
};

// Déclaration des propriétés de la page.
export async function generateMetadata()
{
	return fetchMetadata();
}

// Génération des paramètres pour les pages statiques.
const languages = getLanguages();

export function generateStaticParams()
{
	return languages.map( ( locale ) => ( { locale } ) );
}

// Création des polices de caractères.
const inter = Inter( {
	subsets: [ "latin" ],
	display: "swap"
} );

export default async function Layout( {
	children,
	params
}: Readonly<{
	children: ReactNode;
	params: Promise<{ locale: string }>;
}> )
{
	// Définition de la langue de la page.
	const { locale } = await params;
	const messages = await getMessages();

	setRequestLocale( locale );

	// Vérification du support de la langue.
	if ( !languages.includes( locale ) )
	{
		return null;
	}

	// Définition des données du serveur.
	const serverData = {
		locale,
		domain: await getDomain(),
		offset: getTimeZoneOffset(),
		timezone: getTimeZoneName()
	};

	// Affichage du rendu HTML de la page.
	return (
		<html
			lang={locale}
			className={`text-foreground light:bg-[whitesmoke] antialiased ${ inter.className }`}
			suppressHydrationWarning
		>
			{/* En-tête de la page */}
			<head>
				{/* Mise à jour de l'apparence */}
				<script
					dangerouslySetInnerHTML={{
						__html: `
							// Application du thème préféré par le navigateur.
							const element = document.documentElement;
							const target = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";

							element.classList.remove("light", "dark");
							element.classList.add(target);
							element.style.colorScheme = target;

							if (target === "dark") {
								element.classList.add("cc--darkmode");
							}
						`
					}}
				/>
			</head>

			{/* Corps de la page */}
			<body>
				{/* Vidéo en arrière-plan */}
				<video
					loop
					muted
					autoPlay
					className="fixed -z-10 hidden size-full object-none opacity-25 dark:block"
				>
					<source
						src={`${ process.env.__NEXT_ROUTER_BASEPATH }/assets/videos/background.mp4`}
						type="video/mp4"
					/>
				</video>

				{/* Utilisation des traductions */}
				<Providers
					locale={locale}
					messages={messages}
					serverData={serverData}
				>
					{/* Composant enfant */}
					{children}

					{/* Pied de page */}
					<Footer />

					{/* Consentement des cookies */}
					<CookieConsent />
				</Providers>
			</body>
		</html>
	);
}