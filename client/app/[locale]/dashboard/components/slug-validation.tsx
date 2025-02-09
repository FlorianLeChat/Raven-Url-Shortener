//
// Composant de validation du slug personnalisé.
//

"use client";

import { Input } from "@heroui/react";
import { useTranslations } from "next-intl";
import { useContext, useState } from "react";
import { ServerContext } from "@/components/server-provider";
import { checkSlug } from "../actions/check-slug";

export default function SlugValidation()
{
	// Déclaration des variables d'état.
	const serverData = useContext( ServerContext );
	const errorMessages = useTranslations( "errors" );
	const dashboardMessages = useTranslations( "dashboard" );
	const [ isAvailable, setIsAvailable ] = useState( true );

	// Déclaration des constantes.
	let timeoutId: number;

	// Limite le nombre d'actions dans un lapse de temps donnée.
	// https://lodash.com/docs/4.17.15#debounce
	const debounce = ( callback: ( input: string ) => void, delay: number ) => ( input: string ) =>
	{
		clearTimeout( timeoutId );
		timeoutId = window.setTimeout( () => callback( input ), delay );
	};

	// Mise à jour de la validation du slug.
	const updateSlugValidation = debounce( async ( value: string ) =>
	{
		// Vérification de la validité du slug personnalisé.
		if ( !value )
		{
			setIsAvailable( true );
			return;
		}

		// Requête de vérification de la disponibilité du slug.
		const validationCheck = await checkSlug( value );

		if ( !validationCheck.state || "message" in validationCheck )
		{
			alert( validationCheck.message );
			return;
		}

		setIsAvailable( validationCheck.isAvailable );
	}, 500 );

	// Affichage du rendu HTML du composant.
	return (
		<Input
			as="li"
			size="lg"
			name="slug"
			label={dashboardMessages( "slug_label" )}
			onClear={() => setIsAvailable( true )}
			pattern="^[a-zA-Z0-9\-]+$"
			maxLength={50}
			isInvalid={isAvailable ? undefined : true}
			isClearable
			placeholder="my-super-slug"
			description={(
				<p className="text-default-500">
					{dashboardMessages.rich( "slug_description", {
						strong: ( chunks ) => <strong>{chunks}</strong>
					} )}
				</p>
			)}
			errorMessage={isAvailable ? undefined : errorMessages( "already_used_slug" )}
			startContent={(
				<span className="pointer-events-none whitespace-nowrap text-default-400">
					{serverData?.domain}
				</span>
			)}
			onValueChange={updateSlugValidation}
			labelPlacement="outside"
		/>
	);
}