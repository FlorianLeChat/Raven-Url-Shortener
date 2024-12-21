//
// Composant du bouton pour accéder au tableau de bord.
//

"use client";

import { Button } from "@nextui-org/react";
import { useRouter } from "next/navigation";
import { ArrowRight } from "lucide-react";
import { useEffect, useState } from "react";

export default function GatewayButton()
{
	// Déclaration des variables d'état.
	const router = useRouter();
	const [ isLoading, setIsLoading ] = useState( false );

	// Simulation d'une requête HTTP au serveur.
	useEffect( () =>
	{
		router.prefetch( "/dashboard" );

		if ( isLoading )
		{
			setTimeout( () =>
			{
				router.push( "/dashboard" );
				setIsLoading( false );
			}, 3000 );
		}
	}, [ router, isLoading ] );

	// Affichage du rendu HTML du composant.
	return (
		<Button
			size="lg"
			color="success"
			onPress={() => setIsLoading( true )}
			variant="shadow"
			isLoading={isLoading}
			endContent={<ArrowRight width={20} height={20} />}
		>
			Allons-y&nbsp;!
		</Button>
	);
}