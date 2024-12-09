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
	CardHeader,
	CardFooter } from "@nextui-org/react";
import { I18nProvider } from "@react-aria/i18n";

const InputOptions = lazy( () => import( "./input-options" ) );
const SubmitButtons = lazy( () => import( "./submit-buttons" ) );
const CheckboxOptions = lazy( () => import( "./checkbox-options" ) );

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
				className="gap-3 bg-[#0072F5] p-4 text-white"
			>
				{/* Astuce d'utilisation */}
				<Info className="inline-block min-w-[24px]" />
				Vous pouvez à tout moment modifier les paramètres de votre lien,
				même après sa création.
			</CardHeader>

			<CardBody className="gap-10 p-4 pr-0 lg:flex-row">
				{/* Utilisation de i18n */}
				<I18nProvider>
					{/* Options de saisie */}
					<InputOptions />

					{/* Options additionnelles */}
					<CheckboxOptions />
				</I18nProvider>
			</CardBody>

			<CardFooter
				as="footer"
				className="justify-between gap-4 max-sm:flex-col"
			>
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