import { join } from "node:path";
import { existsSync } from "node:fs";
import type { Metadata } from "next";
import { mkdir, readFile, writeFile } from "node:fs/promises";

const fetchWithCache = async ( url: string ) =>
{
	const response = await fetch( url, { cache: "force-cache" } );

	return response.json() as Promise<Record<string, string>>;
};

export async function fetchMetadata(): Promise<Metadata & { source: string }>
{
	const folderPath = join( process.cwd(), "data" );
	const filePath = join( folderPath, "metadata.json" );

	await mkdir( folderPath, { recursive: true } );

	if ( existsSync( filePath ) )
	{
		return JSON.parse( await readFile( filePath, "utf8" ) ) as Metadata & {
			source: string;
		};
	}

	const repository = await fetchWithCache(
		"https://api.github.com/repos/FlorianLeChat/Raven-Url-Shortener"
	);

	const author = await fetchWithCache(
		"https://api.github.com/users/FlorianLeChat"
	);

	const commits = await fetchWithCache(
		"https://api.github.com/repos/FlorianLeChat/Raven-Url-Shortener/commits/master"
	);

	const banner = `https://opengraph.githubassets.com/${ commits.sha }/${ repository.full_name }`;
	const title = repository.name.replaceAll( "-", " " );
	const basePath = process.env.NEXT_PUBLIC_BASE_PATH ?? "";
	const url = process.env.NEXT_PUBLIC_ENV === "production"
		? repository.homepage
		: `http://localhost:${ process.env.PORT ?? 3000 }`;

	const metadata = {
		title,
		source: repository.html_url,
		authors: [ { name: author.name, url: author.html_url } ],
		keywords: repository.topics,
		description: repository.description,
		metadataBase: new URL( url ),
		icons: {
			icon: [
				{
					url: `${ basePath }/assets/favicons/16x16.webp`,
					type: "image/webp",
					sizes: "16x16"
				},
				{
					url: `${ basePath }/assets/favicons/32x32.webp`,
					type: "image/webp",
					sizes: "32x32"
				},
				{
					url: `${ basePath }/assets/favicons/48x48.webp`,
					type: "image/webp",
					sizes: "48x48"
				},
				{
					url: `${ basePath }/assets/favicons/192x192.webp`,
					type: "image/webp",
					sizes: "192x192"
				},
				{
					url: `${ basePath }/assets/favicons/512x512.webp`,
					type: "image/webp",
					sizes: "512x512"
				}
			],
			apple: [
				{
					url: `${ basePath }/assets/favicons/180x180.webp`,
					type: "image/webp",
					sizes: "180x180"
				}
			]
		},

		// Informations pour les moteurs de recherche.
		openGraph: {
			url,
			type: "website",
			title,
			description: repository.description,
			images: [
				{
					url: banner
				}
			]
		},

		// Informations pour la plate-forme Twitter.
		twitter: {
			title,
			creator: `@${ author.twitter_username }`,
			description: repository.description,
			images: [
				{
					url: banner
				}
			]
		}
	};

	await writeFile( filePath, JSON.stringify( metadata ) );

	return metadata;
}