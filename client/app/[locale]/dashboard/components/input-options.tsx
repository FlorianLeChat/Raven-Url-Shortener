//
// Composant des options de saisie du formulaire.
//

"use client";

import { Link2 } from "lucide-react";
import { useTranslations } from "next-intl";
import { lazy, useContext } from "react";
import { DatePicker, Input } from "@heroui/react";
import { getLocalTimeZone, now } from "@internationalized/date";
import { ServerContext } from "@/components/server-provider";

const SlugValidation = lazy( () => import( "./slug-validation" ) );

export default function InputOptions()
{
	// Déclaration des constantes.
	const dateNow = now( getLocalTimeZone() ); // https://github.com/heroui-inc/heroui/discussions/4711
	const minTime = dateNow.add( { days: 1 } );
	const maxTime = dateNow.add( { years: 1 } );

	// Déclaration des variables d'état.
	const messages = useTranslations( "dashboard" );
	const serverData = useContext( ServerContext );

	// Affichage du rendu HTML du composant.
	return (
		<ul className="flex flex-col gap-5 md:mr-4 lg:w-1/2">
			{/* Lien à raccourcir */}
			<Input
				as="li"
				size="lg"
				type="url"
				name="url"
				label={messages( "url_label" )}
				pattern="https?://.*"
				required
				isRequired
				isClearable
				placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
				description={(
					<p className="text-default-500">
						{messages.rich( "url_description", {
							strong: ( chunks ) => <strong>{chunks}</strong>
						} )}
					</p>
				)}
				labelPlacement="outside"
				startContent={<Link2 className="mr-1" />}
			/>

			{/* Slug personnalisé */}
			<SlugValidation />

			{/* Date de publication */}
			<DatePicker
				as="li"
				size="lg"
				name="expiration"
				label={messages( "expiration_label" )}
				minValue={minTime}
				maxValue={maxTime}
				className="!pb-9"
				description={(
					<p className="text-default-500">
						{messages.rich( "expiration_description", {
							strong: ( chunks ) => <strong>{chunks}</strong>,
							offset: String( serverData?.offset ),
							timezone: String( serverData?.timezone )
						} )}
					</p>
				)}
				labelPlacement="outside"
				showMonthAndYearPickers
				suppressHydrationWarning
			/>
		</ul>
	);
}