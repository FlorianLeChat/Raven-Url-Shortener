//
// Route vers la page de redirection d'un lien raccourci.
//

// Importation des dépendances.
import { lazy } from "react";
import type { Metadata } from "next";
import { getLinkDetails } from "../../dashboard/actions/get-link-details";
import { redirect, RedirectType } from "next/navigation";
import { getTranslations, setRequestLocale } from "next-intl/server";

// Importation des fonctions utilitaires.
import { fetchMetadata } from "@/utilities/metadata";

// Importation des composants.
const RedirectionContainer = lazy(
	() => import( "../components/redirection-container" )
);

// Déclaration des propriétés de la page.
export async function generateMetadata(): Promise<Metadata>
{
	const metadata = await fetchMetadata();
	const messages = await getTranslations();

	return {
		title: `${ messages( "header.redirection" ) } – ${ metadata.title }`
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
	//  rediriger l'utilisateur vers la page d'accueil.
	if ( !details.state || "message" in details )
	{
		redirect( "/?error=not-found", RedirectType.push );
	}

	if ( !details.data.enabled )
	{
		redirect( "/?error=disabled", RedirectType.push );
	}

	// Envoi d'une requête de mise à jour de la date de dernière visite.
	fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/link/${ id }`, {
		body: JSON.stringify( { field: "visitedAt" } ), // Merci Symfony... https://github.com/symfony/symfony/issues/59331
		method: "PATCH"
	} );

	// Affichage du rendu HTML de la page.
	return (
		<>
			{/* En-tête de la page */}
			<header className="container mx-auto max-w-[1440px] p-4 md:p-8">
				<h1 className="inline bg-gradient-to-b from-[#FF705B] to-[#FFB457] bg-clip-text text-4xl font-semibold tracking-tight text-transparent lg:text-5xl">
					{messages( "redirect.title" )}
				</h1>

				<h2 className="mt-2 text-3xl font-semibold tracking-tight lg:text-4xl">
					{messages( "redirect.subtitle" )}
				</h2>

				<p className="my-2 w-full max-w-full text-lg font-normal text-default-500 md:w-1/2 lg:text-xl">
					{messages.rich( "redirect.description", {
						strong: ( children ) => <strong>{children}</strong>
					} )}
				</p>
			</header>

			{/* Contenu de la page */}
			<main className="container mx-auto max-w-[1440px] p-4 pb-8 pt-0 md:p-8 md:pt-0">
				{/* Conteneur de la redirection */}
				<RedirectionContainer details={details.data} />
			</main>
		</>
	);
}