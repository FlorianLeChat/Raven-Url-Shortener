//
// Composant de validation du slug personnalisé.
//

"use client";

import debounce from "lodash/debounce";
import { addToast, Input } from "@heroui/react";
import { useTranslations } from "next-intl";
import { useContext, useState } from "react";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";
import { ServerContext } from "@/components/server-provider";

type SlugCheckResponse = ErrorProperties | {
	available: boolean;
};

export default function SlugValidation()
{
	// Déclaration des variables d'état.
	const messages = useTranslations();
	const serverData = useContext( ServerContext );
	const [ isAvailable, setIsAvailable ] = useState( true );

	// Vérification de la disponibilité du slug personnalisé auprès du back-end PHP.
	const checkSlug = async ( slug: string ) =>
	{
		const data = new FormData();
		data.set( "slug", slug );

		try
		{
			const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/v1/slug`, {
				body: data,
				method: "POST"
			} );

			const json = ( await response.json() ) as SlugCheckResponse;

			console.log( "Slug availability checking response." );
			console.table( json );

			if ( response.ok && "available" in json )
			{
				return {
					state: true,
					available: json.available
				};
			}

			return {
				state: false,
				message: messages( "errors.slug.check_failed" )
			};
		}
		catch ( error )
		{
			console.log( "An error occurred while fetching the slug availability." );
			console.table( error );

			return {
				state: false,
				message: messages( "errors.generic_unknown" )
			};
		}
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
			addToast( {
				color: "danger",
				title: messages( "errors.check_error" ),
				description: validationCheck.message
			} );

			return;
		}

		setIsAvailable( validationCheck.available );
	}, 500 );

	// Affichage du rendu HTML du composant.
	return (
		<Input
			as="li"
			size="lg"
			name="slug"
			label={messages( "dashboard.slug_label" )}
			onClear={() => setIsAvailable( true )}
			pattern="^[a-zA-Z0-9\-]+$"
			maxLength={50}
			isInvalid={isAvailable ? undefined : true}
			isClearable
			placeholder="my-super-slug"
			description={(
				<p className="text-default-500">
					{messages.rich( "dashboard.slug_description", {
						strong: ( chunks ) => <strong>{chunks}</strong>
					} )}
				</p>
			)}
			errorMessage={isAvailable ? undefined : messages( "errors.slug.already_used" )}
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