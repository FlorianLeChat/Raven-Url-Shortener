//
// Composant des options de saisie du formulaire.
//

"use client";

import { Link2 } from "lucide-react";
import { useContext } from "react";
import { DatePicker, Input } from "@heroui/react";
import { getLocalTimeZone, now } from "@internationalized/date";
import { ServerContext } from "@/components/server-provider";

export default function InputOptions()
{
	// Déclaration des constantes.
	const dateNow = now( getLocalTimeZone() );
	const minTime = dateNow.add( { days: 1 } );
	const maxTime = dateNow.add( { years: 1 } );

	// Déclaration des variables d'état.
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
				label="Lien Internet à raccourcir"
				pattern="https?://.*"
				required
				className="w-100"
				isRequired
				isClearable
				placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
				description={(
					<p className="text-default-500">
						Votre lien doit commencer par <strong>http://</strong>{" "}
						ou <strong>https://</strong> et doit être accessible
						publiquement. Les autres protocoles sont ignorés pour
						des raisons de sécurité.
					</p>
				)}
				labelPlacement="outside"
				startContent={<Link2 className="mr-1" />}
			/>

			{/* Slug personnalisé */}
			<Input
				as="li"
				size="lg"
				name="slug"
				label="Slug personnalisé"
				maxLength={50}
				isClearable
				placeholder="my-super-slug"
				description={(
					<p className="text-default-500">
						Un slug est une chaîne de caractères qui identifie de{" "}
						<strong>manière unique</strong> une ressource sur
						Internet. Vous avez la possibilité de personnaliser le
						slug de votre lien pour le rendre plus facile à
						mémoriser.
					</p>
				)}
				startContent={(
					<span className="pointer-events-none whitespace-nowrap text-default-400">
						{serverData?.domain}
					</span>
				)}
				labelPlacement="outside"
			/>

			{/* Date de publication */}
			<DatePicker
				as="li"
				size="lg"
				name="expiration"
				label="Date d'expiration"
				minValue={minTime}
				maxValue={maxTime}
				className="!pb-9"
				description={(
					<p className="text-default-500">
						Votre lien sera automatiquement{" "}
						<strong>désactivé</strong> et <strong>supprimé</strong>{" "}
						après cette date. Le fuseau horaire actuellement utilisé
						est <strong>{serverData?.offset}</strong> (
						{serverData?.timezone}).
					</p>
				)}
				labelPlacement="outside"
				showMonthAndYearPickers
				suppressHydrationWarning
			/>
		</ul>
	);
}