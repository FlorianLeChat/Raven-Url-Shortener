//
// Route vers le tableau de bord du site Internet.
//

// Importation des dépendances.
import { lazy } from "react";
import { headers } from "next/headers";
import { setRequestLocale } from "next-intl/server";

// Importation des composants.
import ServerProvider from "@/components/server-provider";

const Header = lazy( () => import( "@/components/header" ) );
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
			<Header />

			{/* Contenu de la page */}
			<main className="container mx-auto max-w-[1440px] p-4 pt-0 md:p-8 md:pt-0">
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