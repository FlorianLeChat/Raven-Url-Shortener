//
// Structure HTML générale des mentions légales.
//

// Importation des dépendances.
import type { Metadata } from "next";
import { type ReactNode } from "react";
import { getTranslations, setRequestLocale } from "next-intl/server";

// Importation des fonctions utilitaires.
import { fetchMetadata } from "@/utilities/metadata";

// Déclaration des propriétés de la page.
export async function generateMetadata(): Promise<Metadata>
{
	const metadata = await fetchMetadata();
	const messages = await getTranslations();

	return {
		title: `${ messages( "header.legal" ) } – ${ metadata.title }`
	};
}

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

	setRequestLocale( locale );

	// Déclaration des constantes.
	const messages = await getTranslations();

	// Affichage du rendu HTML de la page.
	return (
		<>
			{/* En-tête de la page */}
			<header className="container mx-auto max-w-[1440px] p-4 md:p-8 md:pb-4">
				<h1 className="inline bg-gradient-to-b from-[#D32F2F] to-[#EF5350] bg-clip-text text-4xl font-semibold tracking-tight text-transparent lg:text-5xl">
					{messages( "legal.title" )}
				</h1>

				<h2 className="mt-2 text-3xl font-semibold tracking-tight lg:text-4xl">
					{messages( "legal.subtitle" )}
				</h2>

				<p className="my-2 w-full max-w-full text-lg font-normal text-default-500 md:w-1/2 lg:text-xl">
					{messages( "legal.description" )}
				</p>
			</header>

			{/* Contenu de la page */}
			<main className="container mx-auto max-w-[1440px] p-4 pb-8 pt-0 md:p-8 md:pt-0">
				{children}
			</main>
		</>
	);
}