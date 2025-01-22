//
// Composant du conteneur général du récapitulatif.
//

"use client";

import { lazy } from "react";
import NextImage from "next/image";
import { useLocale } from "next-intl";
import { formatDate } from "@/utilities/date";
import { CircleCheckBig } from "lucide-react";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import { Card, Image, Snippet, CardBody, CardHeader } from "@heroui/react";

const SummaryActions = lazy( () => import( "./summary-actions" ) );

export default function SummaryContainer( {
	domain,
	details
}: Readonly<{ domain: string; details: LinkProperties }> )
{
	// Déclaration des variables d'état.
	const locale = useLocale();

	// Dates de création et de dernière mise à jour du lien.
	const createDate = formatDate( new Date( details.createdAt.date ), locale );
	const updateDate = details.updatedAt?.date
		? formatDate( new Date( details.updatedAt.date ), locale )
		: "jamais";

	// Message contenant les dates de création et de mise à jour.
	let dateMessage = `Votre lien raccourci a été créé le ${ createDate }`;

	if ( details.updatedAt?.date )
	{
		dateMessage += `, mis à jour le ${ updateDate }.`;
	}
	else
	{
		dateMessage += ".";
	}

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
				{dateMessage}
			</CardHeader>

			<CardBody className="flex-col gap-5 p-4 xl:flex-row">
				<Image
					as={NextImage}
					src={details.qrCode ?? ""}
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
						{domain + details.slug}
					</Snippet>

					<Snippet size="lg" className="overflow-auto" hideSymbol>
						{domain + details.id}
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