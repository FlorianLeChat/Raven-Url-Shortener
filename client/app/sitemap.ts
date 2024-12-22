//
// Route vers le fichier du plan du site.
//  Source : https://nextjs.org/docs/app/api-reference/file-conventions/metadata/sitemap
//
import { fetchMetadata } from "@/utilities/metadata";

export default async function Sitemap()
{
	// Déclaration des constantes.
	const date = new Date();
	const baseUrl = new URL( ( await fetchMetadata() )?.metadataBase ?? "" );

	// Génération du plan du site.
	return [
		{
			// Page d'accueil.
			url: baseUrl,
			lastModified: date
		}
	];
}