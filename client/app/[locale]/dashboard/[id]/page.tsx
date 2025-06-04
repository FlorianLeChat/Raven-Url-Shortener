//
// Route vers la page de récapitulatif de création d'un lien raccourci.
//

// Importation des dépendances.
import qrCode from "qrcode";
import { lazy } from "react";
import type { Metadata } from "next";
import { redirect, RedirectType } from "next/navigation";
import { getTranslations, setRequestLocale } from "next-intl/server";

// Importation des fonctions utilitaires.
import { getDomain } from "@/utilities/server";
import { fetchMetadata } from "@/utilities/metadata";
import { getLinkDetails } from "../actions/get-link-details";

// Importation des composants.
const ActionButtons = lazy( () => import( "../components/action-buttons" ) );
const SummaryContainer = lazy( () => import( "../components/summary-container" ) );

// Déclaration des propriétés de la page.
export async function generateMetadata(): Promise<Metadata>
{
	const metadata = await fetchMetadata();
	const messages = await getTranslations();

	return {
		title: `${ messages( "header.summary" ) } – ${ metadata.title }`
	};
}

// Affichage de la page.
export default async function Page( {
	params
}: Readonly<{
	params: Promise<{ id: string; locale: string }>;
}> )
{
	// Définition de la langue de la page.
	const { id, locale } = await params;

	setRequestLocale( locale );

	// Déclaration des constantes.
	const details = await getLinkDetails( id );
	const messages = await getTranslations();

	// Vérification de l'état de la réponse pour
	//  rediriger l'utilisateur vers le tableau de bord.
	if ( !details.state || "message" in details )
	{
		redirect( "/dashboard?error=not-found", RedirectType.push );
	}

	// Génération du code QR.
	const domain = await getDomain();

	details.data.qrCode = await qrCode.toDataURL( domain + id );

	// Affichage du rendu HTML de la page.
	return (
		<>
			{/* En-tête de la page */}
			<header className="container mx-auto max-w-[1440px] p-4 md:p-8">
				<h1 className="inline bg-gradient-to-b from-[#FF72E1] to-[#F54C7A] bg-clip-text text-4xl font-semibold tracking-tight text-transparent lg:text-5xl">
					{messages( "summary.title" )}
				</h1>

				<h2 className="mt-2 text-3xl font-semibold tracking-tight lg:text-4xl">
					{messages( "summary.subtitle" )}
				</h2>

				<p className="my-2 w-full max-w-full text-lg font-normal text-default-500 md:w-1/2 lg:text-xl">
					{messages( "summary.description" )}
				</p>
			</header>

			{/* Contenu de la page */}
			<main className="container mx-auto max-w-[1440px] p-4 pb-8 pt-0 md:p-8 md:pt-0">
				{/* Boutons d'action */}
				<ActionButtons />

				{/* Conteneur du récapitulatif */}
				<SummaryContainer domain={domain} details={details.data} />
			</main>
		</>
	);
}