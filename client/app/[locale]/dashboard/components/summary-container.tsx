//
// Composant du conteneur général du récapitulatif.
//

"use client";

import NextImage from "next/image";
import { CircleCheckBig } from "lucide-react";
import { Card, Image, Snippet, CardBody, CardHeader } from "@nextui-org/react";

export default function SummaryContainer( {
	qrCode
}: Readonly<{ qrCode: string }> )
{
	// Affichage du rendu HTML du composant.
	return (
		<Card
			as="section"
			className="bg-white dark:bg-default-400/10"
			isBlurred
			isFooterBlurred
		>
			<CardHeader
				as="header"
				className="gap-3 bg-success-700 p-4 text-white dark:bg-success-200"
			>
				{/* Astuce d'utilisation */}
				<CircleCheckBig className="inline-block min-w-[24px]" />
				Votre lien a été créé le 23 septembre 2021 à 14:37:42.
			</CardHeader>

			<CardBody className="gap-5 p-4">
				<div className="flex flex-row gap-5">
					<Image
						as={NextImage}
						src={qrCode}
						alt="NextUI hero Image"
						width={200}
						height={200}
					/>

					<div className="flex flex-col gap-3">
						<h3 className="text-xl font-semibold">
							Liens à votre disposition
						</h3>

						<Snippet size="lg">
							https://url.florian-dev.fr/1A7hby
						</Snippet>

						<Snippet size="lg">
							https://url.florian-dev.fr/dffbd949-5144-477f-b94e-f6b63ec5b412
						</Snippet>

						<p className="text-sm text-default-500">
							Ces liens Internet sont disponibles pour toute la
							durée de vie de votre lien raccourci.
							<br />
							Vous pouvez les utiliser sans restriction.
						</p>
					</div>
				</div>
			</CardBody>
		</Card>
	);
}