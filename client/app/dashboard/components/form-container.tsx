//
// Composant du conteneur général du formulaire.
//

"use client";

import { lazy } from "react";
import { Info } from "lucide-react";
import { Link,
	User,
	Card,
	CardBody,
	Progress,
	Accordion,
	CardHeader,
	CardFooter } from "@nextui-org/react";

const InputOptions = lazy( () => import( "./input-options" ) );
const SubmitButtons = lazy( () => import( "./submit-buttons" ) );
const SecurityOptions = lazy( () => import( "./security-options" ) );
const TrackingOptions = lazy( () => import( "./tracking-options" ) );

export default function FormContainer()
{
	// Affichage du rendu HTML du composant.
	return (
		<Card
			as="section"
			className="bg-white dark:bg-default-400/10"
			isBlurred
			isFooterBlurred
		>
			<CardHeader
				as="header"
				className="gap-2 bg-[#0072F5] p-4 text-white"
			>
				{/* Astuce d'utilisation */}
				<Info className="inline-block" />
				Vous pouvez à tout moment modifier les paramètres de votre lien,
				même après sa création.
			</CardHeader>

			<CardBody className="gap-10 p-4 lg:flex-row">
				{/* Options de saisie */}
				<InputOptions />

				<Accordion
					as="article"
					variant="splitted"
					className="p-0 lg:w-1/2"
				>
					{/* Options de sécurité */}
					<SecurityOptions />

					{/* Options de suivi et de statistiques */}
					<TrackingOptions />
				</Accordion>
			</CardBody>

			<CardFooter as="footer" className="justify-between gap-2">
				{/* Boutons de soumission */}
				<SubmitButtons />

				{/* Barre de progression */}
				<Progress
					size="sm"
					label="Contact du serveur... (Essai 1/3)"
					className="max-w-md"
					isIndeterminate
				/>

				{/* Carte du créateur */}
				<div className="mr-1 flex items-center gap-2">
					Made with ❤️ by{" "}
					<User
						name="Florian Trayon"
						description={(
							<Link
								href="https://twitter.com/jrgarciadev"
								size="sm"
								isExternal
							>
								@Florian Trayon
							</Link>
						)}
						avatarProps={{
							src: "https://avatars.githubusercontent.com/u/26360935?v=4"
						}}
					/>
				</div>
			</CardFooter>
		</Card>
	);
}