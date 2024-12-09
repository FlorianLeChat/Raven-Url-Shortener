//
// Composant des options de saisie du formulaire.
//

"use client";

import { Link2 } from "lucide-react";
import { useContext } from "react";
import { DatePicker, Input } from "@nextui-org/react";
import { getLocalTimeZone, now } from "@internationalized/date";
import { ServerContext } from "@/components/server-provider";

export default function InputOptions()
{
	// Déclaration des constantes.
	const maxTime = now( getLocalTimeZone() ).add( {
		years: 1
	} );

	// Déclaration des variables d'état.
	const serverData = useContext( ServerContext );

	// Affichage du rendu HTML du composant.
	return (
		<ul className="flex flex-col gap-5 lg:w-1/2">
			{/* Lien à raccourcir */}
			<Input
				as="li"
				size="lg"
				type="url"
				label="Lien Internet à raccourcir"
				required
				className="w-100"
				isRequired
				isClearable
				placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
				description={(
					<>
						Votre lien doit commencer par <strong>http://</strong>{" "}
						ou <strong>https://</strong> et doit être accessible
						publiquement. Les autres protocoles sont ignorés pour
						des raisons de sécurité.
					</>
				)}
				labelPlacement="outside"
				startContent={<Link2 className="mr-1" />}
			/>

			{/* Slug personnalisé */}
			<Input
				as="li"
				type="url"
				size="lg"
				label="Slug personnalisé"
				maxLength={50}
				isClearable
				placeholder="my-super-slug"
				description={(
					<>
						Un slug est une chaîne de caractères qui identifie de{" "}
						<strong>manière unique</strong> une ressource sur
						Internet. Vous avez la possibilité de personnaliser le
						slug de votre lien pour le rendre plus facile à
						mémoriser.
					</>
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
				label="Date d'expiration"
				maxValue={maxTime}
				className="!pb-9"
				description={(
					<>
						Votre lien sera automatiquement{" "}
						<strong>désactivé</strong> et <strong>supprimé</strong>{" "}
						après cette date. Le fuseau horaire actuellement utilisé
						est <strong>{serverData?.offset}</strong> (
						{serverData?.timezone}).
					</>
				)}
				labelPlacement="outside"
				showMonthAndYearPickers
				suppressHydrationWarning
			/>
		</ul>
	);
}