//
// Composant du consentement des mentions légales.
//

"use client";

import { Link,
	Modal,
	Button,
	ModalBody,
	ModalHeader,
	ModalFooter,
	ModalContent,
	useDisclosure } from "@heroui/react";
import { useTranslations } from "next-intl";
import { usePathname, useRouter } from "next/navigation";
import { useEffect } from "react";

export default function LegalConsent()
{
	// Déclaration des variables d'état.
	const router = useRouter();
	const pathname = usePathname();
	const messages = useTranslations( "index.consent" );
	const { isOpen, onOpen, onClose } = useDisclosure();

	// Redirection vers le tableau de bord si le consentement est accepté.
	const onAcceptConsent = () =>
	{
		onClose();

		localStorage.setItem( "NEXT_CONSENT", new Date().toISOString() );

		if ( pathname === "/" )
		{
			router.push( "/dashboard" );
		}
	};

	// Redirection vers la page d'accueil si le consentement est refusé.
	const onRefuseConsent = () =>
	{
		onClose();

		if ( pathname !== "/" )
		{
			router.push( "/" );
		}
	};

	// Détection du consentement de l'utilisateur.
	useEffect( () =>
	{
		if ( !localStorage.getItem( "NEXT_CONSENT" ) )
		{
			onOpen();
		}
	}, [ onOpen ] );

	// Affichage du rendu HTML du composant
	return (
		<Modal
			isOpen={isOpen}
			onClose={onClose}
			backdrop="blur"
			isDismissable={false}
			hideCloseButton
			isKeyboardDismissDisabled
		>
			<ModalContent>
				<ModalHeader className="flex flex-col gap-1">
					{messages( "title" )}
				</ModalHeader>

				<ModalBody>
					{messages( "description" )}
					<Link
						href="/legal"
						color="primary"
						isExternal
						showAnchorIcon
					>
						{messages( "button" )}
					</Link>
				</ModalBody>

				<ModalFooter>
					<Button color="default" onPress={onRefuseConsent}>
						{messages( "refuse" )}
					</Button>

					<Button color="success" onPress={onAcceptConsent}>
						{messages( "accept" )}
					</Button>
				</ModalFooter>
			</ModalContent>
		</Modal>
	);
}