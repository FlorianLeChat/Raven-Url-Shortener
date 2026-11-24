import { fetchMetadata } from "@/utilities/metadata";
import { type MetadataRoute } from "next";

export default async function manifest(): Promise<MetadataRoute.Manifest>
{
	const metadata = await fetchMetadata();
	const basePath = process.env.NEXT_PUBLIC_BASE_PATH ?? "";

	return {
		name: metadata.title as string,
		display: "standalone",
		start_url: `${ basePath }/`,
		short_name: metadata.title as string,
		description: metadata.description as string,
		theme_color: "#0072f5",
		background_color: "#161a1c",
		icons: [
			{
				src: `${ basePath }/assets/favicons/16x16.webp`,
				type: "image/webp",
				sizes: "16x16"
			},
			{
				src: `${ basePath }/assets/favicons/32x32.webp`,
				type: "image/webp",
				sizes: "32x32"
			},
			{
				src: `${ basePath }/assets/favicons/48x48.webp`,
				type: "image/webp",
				sizes: "48x48"
			},
			{
				src: `${ basePath }/assets/favicons/180x180.webp`,
				type: "image/webp",
				sizes: "180x180"
			},
			{
				src: `${ basePath }/assets/favicons/192x192.webp`,
				type: "image/webp",
				sizes: "192x192"
			},
			{
				src: `${ basePath }/assets/favicons/512x512.webp`,
				type: "image/webp",
				sizes: "512x512"
			}
		]
	};
}