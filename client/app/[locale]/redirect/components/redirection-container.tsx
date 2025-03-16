//
// Composant du conteneur général de la redirection.
//

"use client";

import { lazy } from "react";
import { History } from "lucide-react";
import { formatDate } from "@/utilities/date";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import { useLocale, useTranslations } from "next-intl";
import { Card, Snippet, CardBody, CardHeader } from "@heroui/react";

const AcceptRedirection = lazy( () => import( "./accept-redirection" ) );
const ReportRedirection = lazy( () => import( "./report-redirection" ) );

export default function RedirectionContainer( {
	details
}: Readonly<{ details: LinkProperties }> )
{
	// Déclaration des variables d'état.
	const locale = useLocale();
	const messages = useTranslations( "redirect" );

	// Dates de création et de dernière mise à jour du lien.
	const createDate = formatDate( new Date( details.createdAt.date ), locale );
	const updateDate = details.updatedAt?.date
		? formatDate( new Date( details.updatedAt.date ), locale )
		: undefined;

	// Message contenant les dates de création et de mise à jour.
	const dateMessage = messages( "hint", {
		isUpdated: !!updateDate,
		createDate,
		updateDate
	} );

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
				className="gap-3 bg-warning-700 p-4 text-white dark:bg-warning-200"
			>
				{/* Date de création */}
				<History className="inline-block min-w-[24px]" />
				{dateMessage}
			</CardHeader>

			<CardBody className="flex-col gap-5 p-4 xl:flex-row">
				{/* Liens de partage */}
				<div className="flex w-[inherit] max-w-full flex-col gap-3">
					<h3 className="text-xl font-semibold">
						{messages( "redirection" )}
					</h3>

					<Snippet
						size="lg"
						hideSymbol
						classNames={{
							pre: "overflow-auto whitespace-pre-wrap"
						}}
					>
						{details.url}
					</Snippet>
				</div>

				{/* Actions rapides */}
				<ul className="flex gap-3 max-md:flex-col md:items-end">
					<li>
						<AcceptRedirection details={details} />
					</li>

					<li>
						<ReportRedirection id={details.id} />
					</li>
				</ul>
			</CardBody>
		</Card>
	);
}