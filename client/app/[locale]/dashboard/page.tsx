//
// Route vers le tableau de bord du site Internet.
//

// Importation des dépendances.
import { lazy } from "react";
import { headers } from "next/headers";
import { setRequestLocale } from "next-intl/server";

// Importation des composants.
import ServerProvider from "@/components/server-provider";

const FormContainer = lazy( () => import( "./components/form-container" ) );
const ActionButtons = lazy( () => import( "./components/action-buttons" ) );

// Affichage de la page.
export default async function Page( {
	params
}: Readonly<{
	params: Promise<{ locale: string }>;
}> )
{
	// Définition de la langue de la page.
	const { locale } = await params;

	setRequestLocale( locale );

	// Déclaration des constantes.
	const requestHeaders = await headers();
	const baseDomain = `${ requestHeaders.get( "x-forwarded-proto" ) ?? "http" }://${ requestHeaders.get( "host" ) }/`;
	const options = Intl.DateTimeFormat().resolvedOptions();

	// Récupération du fuseau horaire actuel et de son décalage.
	//  Décalage en minutes (exemple : -60 pour UTC+1) et conversion en heures.
	const offsetMinutes = new Date().getTimezoneOffset();
	const offsetHours = -offsetMinutes / 60;
	const offsetString = `UTC${ offsetHours >= 0 ? "+" : "" }${ offsetHours }`;

	// Définition des données du serveur.
	const serverData = {
		domain: baseDomain,
		offset: offsetString,
		timezone: process.env.TZ ?? options.timeZone
	};

	// Affichage du rendu HTML de la page.
	return (
		<>
			{/* En-tête de la page */}
			<header className="container mx-auto max-w-[1440px] p-4 md:p-8 md:pb-4">
				<h1 className="inline bg-gradient-to-b from-[#FF705B] to-[#FFB457] bg-clip-text text-4xl font-semibold tracking-tight text-transparent lg:text-5xl">
					À vous de jouer !
				</h1>

				<h2 className="mt-2 text-3xl font-semibold tracking-tight lg:text-4xl">
					Votre lien n&lsquo;attend que vous.
				</h2>

				<p className="my-2 w-full max-w-full text-lg font-normal text-default-500 md:w-1/2 lg:text-xl">
					Vous pouvez dès à présent façonner le comportement de votre
					lien raccourci en le personnalisant selon vos besoins et vos
					envies.
				</p>
			</header>

			{/* Contenu de la page */}
			<main className="container mx-auto max-w-[1440px] p-4 pb-8 pt-0 md:p-8 md:pt-0">
				<ServerProvider value={serverData}>
					{/* Boutons d'action */}
					<ActionButtons />

					{/* Conteneur du formulaire */}
					<FormContainer />
				</ServerProvider>
			</main>
		</>
	);
}