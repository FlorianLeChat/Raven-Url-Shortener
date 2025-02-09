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

	// Affichage du rendu HTML du composant.
	return (
		<Button
			size="lg"
			color="success"
			onPress={() => router.push( "/dashboard" )}
			variant="shadow"
			endContent={<ArrowRight width={20} height={20} />}
		>
			{messages( "button" )}
		</Button>
	);
}