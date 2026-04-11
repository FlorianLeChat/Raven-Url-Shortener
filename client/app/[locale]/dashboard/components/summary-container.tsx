"use client";

import { lazy } from "react";
import NextImage from "next/image";
import { Card,
	Image,
	Button,
	Snippet,
	addToast,
	CardBody,
	CardHeader,
	CardFooter } from "@heroui/react";
import { useRouter } from "next/navigation";
import { formatDate } from "@/utilities/date";
import { QrCode,
	Terminal,
	ArrowLeft,
	CircleCheckBig,
	SquareArrowOutUpRight } from "lucide-react";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import { useLocale, useTranslations } from "next-intl";

const SummaryActions = lazy( () => import( "./summary-actions" ) );

export default function SummaryContainer( {
	domain,
	details
}: Readonly<{ domain: string; details: LinkProperties }> )
{
	const locale = useLocale();
	const router = useRouter();
	const messages = useTranslations( "summary" );

	const createDate = formatDate( new Date( details.createdAt.date ), locale );
	const updateDate = details.updatedAt?.date
		? formatDate( new Date( details.updatedAt.date ), locale )
		: undefined;

	const dateMessage = messages( "hint", {
		isUpdated: String( !!updateDate ),
		createDate: String( createDate ),
		updateDate: String( updateDate )
	} );

	const downloadQrCode = () =>
	{
		const link = document.createElement( "a" );
		link.href = details.qrCode ?? "";
		link.download = `${ details.slug }.png`;

		document.body.appendChild( link );

		link.click();
		link.remove();
	};

	const copyApiKey = async () =>
	{
		if ( !details.apiKey )
		{
			return;
		}

		await navigator.clipboard.writeText( details.apiKey );

		addToast( {
			title: messages( "api_key.title" ),
			color: "success",
			timeout: 10000,
			description: messages( "api_key.description" )
		} );
	};

	return (
		<Card
			as="section"
			className="dark:bg-default-100/30 bg-white"
			isBlurred
			isFooterBlurred
		>
			<CardHeader
				as="header"
				className="bg-success-700 dark:bg-success-200 gap-3 p-4 text-white"
			>
				<CircleCheckBig className="inline-block min-w-6" />
				{dateMessage}
			</CardHeader>

			<CardBody className="flex-col gap-5 p-4 xl:flex-row">
				<Image
					as={NextImage}
					src={details.qrCode ?? ""}
					alt={messages( "qr_code" )}
					width={200}
					height={200}
					className="max-h-50 min-h-50 max-w-50 min-w-50"
				/>

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
						disableTooltip
					>
						{domain + details.slug}
					</Snippet>

					<Snippet
						size="lg"
						hideSymbol
						classNames={{
							pre: "overflow-auto whitespace-pre-wrap"
						}}
						disableTooltip
					>
						{domain + details.id}
					</Snippet>

					<p className="text-default-500 text-sm">
						{messages.rich( "links_description", {
							br: () => <br />
						} )}
					</p>
				</div>

				<SummaryActions />
			</CardBody>

			<CardFooter
				as="footer"
				className="bg-content2/50 items-center gap-3"
			>
				<Button
					type="button"
					variant="flat"
					onPress={() => router.push( "/dashboard" )}
					className="max-sm:min-w-16"
					startContent={<ArrowLeft />}
					data-umami-event="Back to dashboard after shortened link access"
				>
					<span className="hidden lg:inline">
						{messages( "back_to_dashboard" )}
					</span>
				</Button>

				<Button
					type="button"
					variant="flat"
					onPress={() => router.push( details.url )}
					className="max-sm:min-w-16"
					startContent={<SquareArrowOutUpRight />}
					data-umami-event="Go to shortened link"
					data-umami-event-url={details.url}
				>
					<span className="hidden lg:inline">
						{messages( "access_to_link" )}
					</span>
				</Button>

				<Button
					type="button"
					variant="flat"
					onPress={downloadQrCode}
					className="max-sm:min-w-16"
					startContent={<QrCode />}
					data-umami-event="Download QR code for shortened link"
				>
					<span className="hidden lg:inline">
						{messages( "download_qr_code" )}
					</span>
				</Button>

				<Button
					type="button"
					variant="flat"
					onPress={copyApiKey}
					isDisabled={!details.apiKey}
					className="max-sm:min-w-16"
					startContent={<Terminal />}
					data-umami-event="Copy API key to manage shortened link"
				>
					<span className="hidden lg:inline">
						{messages( "copy_api_key" )}
					</span>
				</Button>
			</CardFooter>
		</Card>
	);
}