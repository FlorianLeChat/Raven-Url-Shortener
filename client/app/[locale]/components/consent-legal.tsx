//
// Composant du consentement des mentions légales.
//

"use client";

import { Link } from "@heroui/react";
import { Scale } from "lucide-react";
import { useModal } from "@/components/provider-modal";
import { useEffect } from "react";
import { useTranslations } from "next-intl";
import { usePathname, useRouter } from "next/navigation";

export default function LegalConsent()
{
	// Déclaration des variables d'état.
	const router = useRouter();
	const pathname = usePathname();
	const messages = useTranslations( "index.consent" );
	const { showModal } = useModal();

	// Redirection vers le tableau de bord si le consentement est accepté.
	const onAcceptConsent = () =>
	{
		localStorage.setItem( "NEXT_CONSENT", new Date().toISOString() );
	};

	// Redirection vers la page d'accueil si le consentement est refusé.
	const onRefuseConsent = () =>
	{
		if ( pathname !== "/" )
		{
			router.push( "/" );
		}
	};

	// Ouverture de la boite de dialogue de consentement.
	const openConsentModal = () =>
	{
		showModal( {
			title: messages( "title" ),
			body: (
				<>
					{messages( "description" )}
					<Link
						href="/legal"
						color="primary"
						isExternal
						showAnchorIcon
					>
						{messages( "button" )}
					</Link>
				</>
			),
			icon: <Scale />,
			onOpen: onAcceptConsent,
			onClose: onRefuseConsent,
			cancelText: messages( "refuse" ),
			confirmText: messages( "accept" ),
			confirmColor: "success",
			isDismissable: false,
			hideCloseButton: true
		} );
	};

	// Détection du consentement de l'utilisateur.
	useEffect( () =>
	{
		if ( !localStorage.getItem( "NEXT_CONSENT" ) )
		{
			openConsentModal();
		}
	}, [] );

	// Affichage du rendu HTML du composant
	return null;
}