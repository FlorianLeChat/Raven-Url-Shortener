//
// Composant du bouton d'acceptation de la redirection.
//

"use client";

import { useRouter } from "next/navigation";
import { Button,
	Dropdown,
	ButtonGroup,
	DropdownMenu,
	DropdownItem,
	DropdownTrigger,
	type SharedSelection } from "@heroui/react";
import { useTranslations } from "next-intl";
import { Check, ChevronDownIcon } from "lucide-react";
import type { LinkProperties } from "@/interfaces/LinkProperties";

export default function AcceptRedirection( {
	details
}: Readonly<{ details: LinkProperties }> )
{
	// Déclaration des variables d'état.
	const router = useRouter();
	const messages = useTranslations( "redirect.accept" );

	// Fonction de gestion de l'acceptation de la redirection.
	const onAcceptRedirection = ( selection: SharedSelection ) =>
	{
		// Chrome bloque les cookies ayant une date d'expiration supérieure à 400 jours.
		// https://developer.chrome.com/blog/cookie-max-age-expires
		const date = new Date();
		date.setFullYear( date.getFullYear() + 1 );

		const cookie = `NEXT_REDIRECTION=true; expires=${ date.toUTCString() }; Secure; SameSite=Lax`;

		if ( selection.currentKey === "only-this-url" )
		{
			// Si la redirection n'est acceptée que pour cette URL, on ajoute le cookie
			//  uniquement pour le chemin de l'URL avec son identifiant unique et son slug.
			document.cookie = `${ cookie }; path=/${ details.id }`;

			if ( details.slug )
			{
				document.cookie = `${ cookie }; path=/${ details.slug }`;
			}
		}

		if ( selection.currentKey === "all-url" )
		{
			// Si toutes les redirections ont acceptées, on ajoute le cookie à la racine
			//  du domaine pour couvrir toutes les URL.
			document.cookie = `${ cookie }; path=/`;
		}

		router.push( details.url );
	};

	// Affichage du rendu HTML du composant.
	return (
		<ButtonGroup variant="flat">
			{/* Bouton par défaut */}
			<Button
				size="lg"
				color="success"
				variant="shadow"
				onPress={() => router.push( details.url )}
				aria-label={messages( "default" )}
				startContent={<Check />}
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
					className="max-w-[350px]"
					aria-label="Options de redirection"
					selectionMode="single"
					onSelectionChange={onAcceptRedirection}
				>
					{/* Uniquement cette fois-ci */}
					<DropdownItem
						key="only-once"
						classNames={{
							description: "text-wrap"
						}}
						description={messages( "only_once_description" )}
					>
						{messages( "only_once_title" )}
					</DropdownItem>

					{/* Uniquement pour cette URL */}
					<DropdownItem
						key="only-this-url"
						classNames={{
							description: "text-wrap"
						}}
						description={messages( "only_this_url_description" )}
					>
						{messages( "only_this_url_title" )}
					</DropdownItem>

					{/* Pour toutes les URL */}
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