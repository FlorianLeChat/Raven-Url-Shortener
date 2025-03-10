//
// Modèle de page pour le tableau de bord du site Internet.
//

"use client";

// Importation des dépendances.
import { addToast } from "@heroui/react";
import { useTranslations } from "next-intl";
import { useEffect, type ReactNode } from "react";

// Affichage de la page.
export default function Template( {
	children
}: Readonly<{ children: ReactNode }> )
{
	// Déclaration des variables d'état.
	const messages = useTranslations( "errors.redirection" );

	// Gestion des erreurs de redirection.
	useEffect( () =>
	{
		const parameters = new URLSearchParams( window.location.search );

		switch ( parameters.get( "error" ) )
		{
			case "not-found":
				// Lien introuvable.
				addToast( {
					color: "danger",
					title: messages( "not-found.title" ),
					description: messages( "not-found.description" )
				} );

				break;

			case "disabled":
				// Lien désactivé.
				addToast( {
					color: "danger",
					title: messages( "disabled.title" ),
					description: messages( "disabled.description" )
				} );

				break;

			default:
				break;
		}
	}, [ messages ] );

	// Affichage du rendu HTML de la page.
	return children;
}