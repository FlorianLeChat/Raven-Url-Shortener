//
// Page de chargement des composants asynchrones.
//  Source : https://nextjs.org/docs/app/api-reference/file-conventions/loading
//

"use client";

import { CircularProgress } from "@nextui-org/react";

export default function Loading()
{
	// Affichage du rendu HTML du composant.
	return (
		<div className="absolute flex size-full flex-col items-center justify-center gap-4 text-center text-3xl font-bold uppercase leading-normal sm:text-4xl">
			🔗 Raven Url Shortener
			<CircularProgress size="lg" aria-label="Chargement en cours..." />
		</div>
	);
}