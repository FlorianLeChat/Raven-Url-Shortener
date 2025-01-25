//
// Route vers la page de redirection d'un lien raccourci.
//

// Importation des dépendances.
import { lazy } from "react";
import { setRequestLocale } from "next-intl/server";
import { redirect, RedirectType } from "next/navigation";
import { getLinkDetails } from "../../dashboard/actions/get-link-details";

// Importation des composants.
const RedirectionContainer = lazy( () => import( "../components/redirection-container" ) );

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

	// Vérification de l'état de la réponse
	//  pour afficher le message d'erreur.
	if ( !details.state || "message" in details )
	{
		redirect( "/dashboard", RedirectType.push );
	}

	// Affichage du rendu HTML de la page.
	return (
		<>
			{/* En-tête de la page */}
			<header className="container mx-auto max-w-[1440px] p-4 md:p-8">
				<h1 className="inline bg-gradient-to-b from-[#FF705B] to-[#FFB457] bg-clip-text text-4xl font-semibold tracking-tight text-transparent lg:text-5xl">
					Une seconde...
				</h1>

				<h2 className="mt-2 text-3xl font-semibold tracking-tight lg:text-4xl">
					Vous allez être redirigé vers la page demandée.
				</h2>

				<p className="my-2 w-full max-w-full text-lg font-normal text-default-500 md:w-1/2 lg:text-xl">
					Veuillez noter que vous allez être redirigé vers une autre
					page Web en dehors de Raven Url Shortener.{" "}
					<strong>
						Cette page n&lsquo;est pas hébergée par nos soins et
						nous ne sommes pas responsables de son contenu.
					</strong>
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