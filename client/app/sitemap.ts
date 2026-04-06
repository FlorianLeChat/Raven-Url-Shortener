import { fetchMetadata } from "@/utilities/metadata";

export default async function Sitemap()
{
	// Déclaration des constantes.
	const date = new Date();
	const baseUrl = new URL( ( await fetchMetadata() )?.metadataBase ?? "" );

	return [
		{
			url: baseUrl,
			lastModified: date
		},
		{
			url: baseUrl + "dashboard",
			lastModified: date
		}
	];
}