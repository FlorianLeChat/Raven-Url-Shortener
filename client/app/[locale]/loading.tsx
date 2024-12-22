//
// Page de chargement des composants asynchrones.
//  Source : https://nextjs.org/docs/app/api-reference/file-conventions/loading
//
import { Loader2 } from "lucide-react";
import { fetchMetadata } from "@/utilities/metadata";

export default async function Loading()
{
	// Déclaration des constantes.
	const { title } = ( await fetchMetadata() ) as { title: string };

	// Affichage du rendu HTML de la page.
	return (
		<div className="absolute flex size-full flex-col items-center justify-center gap-4 text-center text-3xl font-bold uppercase leading-normal sm:text-4xl">
			🔗 {title}
			<Loader2 className="size-14 animate-spin text-primary" />
		</div>
	);
}