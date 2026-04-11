"use client";

import { useRouter } from "next/navigation";
import { setCookie } from "@/utilities/cookie";
import { Button,
	Dropdown,
	ButtonGroup,
	DropdownMenu,
	DropdownItem,
	DropdownTrigger,
	type SharedSelection } from "@heroui/react";
import { useTranslations } from "next-intl";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import { Check, ChevronDownIcon } from "lucide-react";

export default function AcceptRedirection( { details }: Readonly<{ details: LinkProperties }> )
{
	const router = useRouter();
	const messages = useTranslations( "redirect.accept" );

	const onAcceptRedirection = ( selection: SharedSelection ) =>
	{
		if ( selection.currentKey === "only-this-url" )
		{
			setCookie( {
				name: "NEXT_REDIRECTION",
				value: "true",
				path: `/${ details.id }`
			} );

			setCookie( {
				name: "NEXT_REDIRECTION",
				value: "true",
				path: `/${ details.slug }`
			} );

			if ( typeof umami !== "undefined" )
			{
				umami.track( "accept-redirection-only-this-url", {
					url: details.url
				} );
			}
		}

		if ( selection.currentKey === "all-url" )
		{
			setCookie( {
				name: "NEXT_REDIRECTION",
				value: "true"
			} );

			if ( typeof umami !== "undefined" )
			{
				umami.track( "accept-redirection-all-url", {
					url: details.url
				} );
			}
		}

		router.push( details.url );
	};

	return (
		<ButtonGroup variant="flat">
			<Button
				size="lg"
				color="success"
				variant="shadow"
				onPress={() => router.push( details.url )}
				aria-label={messages( "default" )}
				startContent={<Check />}
				data-umami-event="accept-redirection-once"
				data-umami-event-url={details.url}
			>
				{messages( "default" )}
			</Button>

			<Dropdown placement="bottom-end">
				<DropdownTrigger>
					<Button
						size="lg"
						color="success"
						variant="shadow"
						isIconOnly
					>
						<ChevronDownIcon />
					</Button>
				</DropdownTrigger>

				<DropdownMenu
					className="max-w-87.5"
					aria-label={messages( "options" )}
					selectionMode="single"
					onSelectionChange={onAcceptRedirection}
				>
					<DropdownItem
						key="only-once"
						classNames={{
							description: "text-wrap"
						}}
						description={messages( "only_once_description" )}
					>
						{messages( "only_once_title" )}
					</DropdownItem>

					<DropdownItem
						key="only-this-url"
						classNames={{
							description: "text-wrap"
						}}
						description={messages( "only_this_url_description" )}
					>
						{messages( "only_this_url_title" )}
					</DropdownItem>

					<DropdownItem
						key="all-url"
						classNames={{
							description: "text-wrap"
						}}
						description={messages( "all_url_description" )}
					>
						{messages( "all_url_title" )}
					</DropdownItem>
				</DropdownMenu>
			</Dropdown>
		</ButtonGroup>
	);
}