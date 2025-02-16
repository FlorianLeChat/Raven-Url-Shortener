//
// Composant du bouton pour accéder au tableau de bord.
//

"use client";

import { Button } from "@heroui/react";
import { useRouter } from "next/navigation";
import { ArrowRight } from "lucide-react";
import { useTranslations } from "next-intl";

export default function GatewayButton()
{
	// Déclaration des variables d'état.
	const router = useRouter();
	const messages = useTranslations( "index.ready" );

	// Redirection vers le tableau de bord si le consentement est accepté.
	const redirectToDashboard = () =>
	{
		if ( localStorage.getItem( "NEXT_CONSENT" ) )
		{
			router.push( "/dashboard" );
		}
		else
		{
			window.location.reload();
		}
	};

	// Affichage du rendu HTML du composant.
	return (
		<Button
			size="lg"
			color="success"
			onPress={redirectToDashboard}
			variant="shadow"
			endContent={<ArrowRight width={20} height={20} />}
		>
			{messages( "button" )}
		</Button>
	);
}