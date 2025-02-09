//
// Structure HTML générale des pages du site.
//  Source : https://nextjs.org/docs/app/building-your-application/routing/pages-and-layouts#root-layout-required
//

// Importation du normalisateur TypeScript.
import "@total-typescript/ts-reset";

// Importation des dépendances.
import pick from "lodash/pick";
import { Inter } from "next/font/google";
import { NextIntlClientProvider } from "next-intl";
import { getMessages, setRequestLocale } from "next-intl/server";
import { lazy, Suspense, type ReactNode } from "react";

// Importation des fonctions utilitaires.
import { logger } from "@/utilities/pino";
import { getDomain,
	getTimeZoneName,
	getTimeZoneOffset } from "@/utilities/server";
import { getLanguages } from "@/utilities/i18n";
import { fetchMetadata } from "@/utilities/metadata";
import { HeroUIProvider } from "@/utilities/hero-ui";

// Importation des types.
import type { Viewport } from "next";

// Importation des composants.
import ServerProvider from "@/components/server-provider";

const Footer = lazy( () => import( "@/components/footer" ) );
const Recaptcha = lazy( () => import( "@/components/recaptcha" ) );
const CookieConsent = lazy( () => import( "@/components/cookie-consent" ) );

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
		logger.error( { source: __dirname, locale }, "Unsupported language" );
		return null;
	}

	// Définition des données du serveur.
	const serverData = {
		domain: await getDomain(),
		offset: getTimeZoneOffset(),
		timezone: getTimeZoneName()
	};

	// Affichage du rendu HTML de la page.
	return (
		<html
			lang={locale}
			className={`text-foreground antialiased light:bg-[whitesmoke] ${ inter.className }`}
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
					className="fixed -z-10 hidden size-full object-none opacity-20 dark:block"
				>
					<source
						src={`${ process.env.__NEXT_ROUTER_BASEPATH }/assets/videos/background.mp4`}
						type="video/mp4"
					/>
				</video>

				{/* Écran de chargement de la page */}
				<Suspense>
					{/* Utilisation des traductions */}
					<NextIntlClientProvider
						locale={locale}
						messages={pick(
							messages,
							"errors",
							"footer",
							"summary",
							"redirect",
							"dashboard",
							"navigation",
							"index.ready",
							"consentModal",
							"preferencesModal"
						)}
						timeZone={process.env.TZ}
					>
						{/* Utilisation de HeroUI */}
						<HeroUIProvider className="flex min-h-screen flex-col">
							<ServerProvider value={serverData}>
								{/* Composant enfant */}
								{children}
							</ServerProvider>

							{/* Pied de page */}
							<Footer />
						</HeroUIProvider>

						{/* Consentement des cookies */}
						<CookieConsent />

						{/* Google reCAPTCHA */}
						<Recaptcha />
					</NextIntlClientProvider>
				</Suspense>
			</body>
		</html>
	);
}