//
// Composant du conteneur général du récapitulatif.
//

"use client";

import { lazy } from "react";
import NextImage from "next/image";
import { Card,
	Image,
	Button,
	Snippet,
	CardBody,
	CardHeader,
	CardFooter } from "@heroui/react";
import { useRouter } from "next/navigation";
import { formatDate } from "@/utilities/date";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import { useLocale, useTranslations } from "next-intl";
import { CircleCheckBig, ArrowLeft, SquareArrowOutUpRight } from "lucide-react";

const SummaryActions = lazy( () => import( "./summary-actions" ) );

export default function SummaryContainer( {
	domain,
	details
}: Readonly<{ domain: string; details: LinkProperties }> )
{
	// Déclaration des variables d'état.
	const locale = useLocale();
	const router = useRouter();
	const messages = useTranslations( "summary" );

	// Dates de création et de dernière mise à jour du lien.
	const createDate = formatDate( new Date( details.createdAt.date ), locale );
	const updateDate = details.updatedAt?.date
		? formatDate( new Date( details.updatedAt.date ), locale )
		: undefined;

	// Message contenant les dates de création et de mise à jour.
	const dateMessage = messages( "hint", {
		isUpdated: String( !!updateDate ),
		createDate: String( createDate ),
		updateDate: String( updateDate )
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
					alt={messages( "qr_code" )}
					width={200}
					height={200}
					className="max-h-[200px] min-h-[200px] min-w-[200px] max-w-[200px]"
				/>

				{/* Liens de partage */}
				<div className="flex max-w-full flex-col gap-3">
					<h3 className="text-xl font-semibold">
						{messages( "links_label" )}
					</h3>

					<Snippet
						size="lg"
						hideSymbol
						classNames={{
							pre: "overflow-auto whitespace-pre-wrap"
						}}
					>
						{domain + details.slug}
					</Snippet>

					<Snippet
						size="lg"
						hideSymbol
						classNames={{
							pre: "overflow-auto whitespace-pre-wrap"
						}}
					>
						{domain + details.id}
					</Snippet>

					<p className="text-sm text-default-500">
						{messages.rich( "links_description", {
							br: () => <br />
						} )}
					</p>
				</div>

				{/* Actions rapides */}
				<SummaryActions />
			</CardBody>

			<CardFooter
				as="footer"
				className="items-start gap-3 bg-content2/50 max-lg:flex-col"
			>
				{/* Bouton de retour au tableau de bord */}
				<Button
					type="button"
					variant="flat"
					onPress={() => router.push( "/dashboard" )}
					startContent={<ArrowLeft />}
				>
					{messages( "back_to_dashboard" )}
				</Button>

				{/* Bouton d'accès au lien */}
				<Button
					type="button"
					variant="flat"
					onPress={() => router.push( details.url )}
					startContent={<SquareArrowOutUpRight />}
				>
					{messages( "access_to_link" )}
				</Button>
			</CardFooter>
		</Card>
	);
}