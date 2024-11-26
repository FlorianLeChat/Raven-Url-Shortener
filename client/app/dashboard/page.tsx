//
// Route vers le tableau de bord du site Internet.
//

// Importation des dépendances.
import { lazy } from "react";
import { headers } from "next/headers";

// Importation des composants.
import ServerProvider from "../components/server-provider";

const Header = lazy( () => import( "../components/header" ) );
const Footer = lazy( () => import( "../components/footer" ) );
const FormContainer = lazy( () => import( "./components/form-container" ) );

export default async function Page()
{
	// Déclaration des constantes.
	const options = Intl.DateTimeFormat().resolvedOptions();
	const requestHeaders = await headers();
	const baseDomain = `${ requestHeaders.get( "x-forwarded-proto" ) || "http" }://${ requestHeaders.get( "host" ) }/`;

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
					<FormContainer />
				</ServerProvider>
			</main>

			{/* Pied de page */}
			<Footer />
		</>
	);
}