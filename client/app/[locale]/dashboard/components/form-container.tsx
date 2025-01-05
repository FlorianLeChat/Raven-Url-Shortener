//
// Composant du conteneur général du formulaire.
//

"use client";

import { Link,
	User,
	Card,
	Button,
	CardBody,
	Progress,
	CardHeader,
	CardFooter } from "@nextui-org/react";
import { I18nProvider } from "@react-aria/i18n";
import { WandSparkles, Info } from "lucide-react";
import { lazy, useRef, useState } from "react";

const InputOptions = lazy( () => import( "./input-options" ) );
const CheckboxOptions = lazy( () => import( "./checkbox-options" ) );

export default function FormContainer()
{
	// Déclaration des variables d'état.
	const submitButton = useRef<HTMLButtonElement | null>( null );
	const [ isLoading, setIsLoading ] = useState( false );

	// Simulation d'une requête HTTP au serveur.
	//  Source : https://github.com/nextui-org/nextui/blob/1485eca48fce8a0acc42fe40590b828c1a90ff48/apps/docs/components/demos/custom-button.tsx#L11-L36
	const simulateRequest = async () =>
	{
		// Calcul de la position du bouton de soumission.
		const { clientWidth, clientHeight } = document.documentElement;
		const boundingBox = submitButton.current?.getBoundingClientRect?.();

		const targetX = boundingBox?.x ?? 0;
		const targetY = boundingBox?.y ?? 0;
		const targetWidth = boundingBox?.width ?? 0;

		// Lancement de l'animation de confettis.
		const targetCenterX = targetX + targetWidth / 2;
		const confetti = ( await import( "canvas-confetti" ) ).default;

		confetti( {
			origin: {
				x: targetCenterX / clientWidth,
				y: targetY / clientHeight
			},
			spread: 70,
			zIndex: 999,
			particleCount: 100,
			disableForReducedMotion: true
		} );

		// Affichage de l'état de chargement.
		setIsLoading( true );

		setTimeout( () =>
		{
			setIsLoading( false );
		}, 3000 );
	};

	// Affichage du rendu HTML du composant.
	return (
		<Card
			as="section"
			className="bg-white dark:bg-default-100/30"
			isBlurred
			isFooterBlurred
		>
			<CardHeader as="header" className="gap-3 bg-primary p-4 text-white">
				{/* Astuce d'utilisation */}
				<Info className="inline-block min-w-[24px]" />
				Vous pourrez à tout moment modifier l&lsquo;ensemble des
				paramètres de votre lien, même après sa création.
			</CardHeader>

			<CardBody className="p-4 max-md:gap-6 lg:flex-row">
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
				className="justify-between gap-4 bg-content2/50 max-sm:flex-col"
			>
				{/* Boutons de soumission */}
				<Button
					ref={submitButton}
					size="lg"
					color="success"
					variant="shadow"
					onPress={simulateRequest}
					isLoading={isLoading}
					startContent={isLoading ? null : <WandSparkles />}
				>
					Créer un nouveau raccourci
				</Button>

				{/* Barre de progression */}
				{isLoading && (
					<Progress
						size="sm"
						label="Contact du serveur... (Essai 1/3)"
						className="max-w-md"
						isIndeterminate
					/>
				)}

				{/* Carte du créateur */}
				<div className="mr-1 flex items-center gap-2">
					Fait avec ❤️ par{" "}
					<User
						name="Florian Trayon"
						description={(
							<Link
								href="https://www.florian-dev.fr/"
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