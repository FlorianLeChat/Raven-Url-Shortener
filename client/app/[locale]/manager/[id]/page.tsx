//
// Route vers la page de gestion d'un lien raccourci.
//

// Importation des dépendances.
import { setRequestLocale } from "next-intl/server";

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

	// Affichage du rendu HTML de la page.
	return (
		<>
			{/* Contenu de la page */}
			<main className="container mx-auto max-w-[1440px] p-4 pt-0 md:p-8 md:pt-0" />
		</>
	);
}