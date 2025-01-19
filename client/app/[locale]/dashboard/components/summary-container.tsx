//
// Composant du conteneur général du récapitulatif.
//

"use client";

import NextImage from "next/image";
import { CircleCheckBig } from "lucide-react";
import { lazy, useContext } from "react";
import { Card, Image, Snippet, CardBody, CardHeader } from "@heroui/react";
import { ServerContext } from "../../components/server-provider";

const SummaryActions = lazy( () => import( "./summary-actions" ) );

export default function SummaryContainer( {
	qrCode
}: Readonly<{ qrCode: string }> )
{
	// Déclaration des variables d'état.
	const serverData = useContext( ServerContext );

	// Affichage du rendu HTML du composant.
	return (
		<Card
			as="section"
			className="bg-white dark:bg-default-100/30"
			isBlurred
			isFooterBlurred
		>
			<CardHeader
				as="header"
				className="gap-3 bg-success-700 p-4 text-white dark:bg-success-200"
			>
				{/* Date de création */}
				<CircleCheckBig className="inline-block min-w-[24px]" />
				Votre lien a été créé le 23 septembre 2021 à 14:37:42.
			</CardHeader>

			<CardBody className="flex-col gap-5 p-4 xl:flex-row">
				<Image
					as={NextImage}
					src={qrCode}
					alt="Code QR de votre lien raccourci"
					width={200}
					height={200}
					className="max-h-[200px] min-h-[200px] min-w-[200px] max-w-[200px]"
				/>

				{/* Liens de partage */}
				<div className="flex max-w-full flex-col gap-3">
					<h3 className="text-xl font-semibold">
						Liens à votre disposition
					</h3>

					<Snippet size="lg" className="overflow-auto" hideSymbol>
						{`${ serverData?.domain }1A7hby`}
					</Snippet>

					<Snippet size="lg" className="overflow-auto" hideSymbol>
						{`${ serverData?.domain }dffbd949-5144-477f-b94e-f6b63ec5b412`}
					</Snippet>

					<p className="text-sm text-default-500">
						Ces liens Internet sont disponibles pour toute la durée
						de vie de votre lien raccourci.
						<br />
						Vous pouvez les utiliser sans restriction.
					</p>
				</div>

				{/* Actions rapides */}
				<SummaryActions />
			</CardBody>
		</Card>
	);
}