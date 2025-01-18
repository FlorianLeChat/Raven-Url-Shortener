//
// Composant du conteneur général du formulaire.
//

"use client";

import { Form,
	Link,
	User,
	Card,
	Button,
	Progress,
	CardBody,
	CardHeader,
	CardFooter } from "@nextui-org/react";
import { useRouter } from "next/navigation";
import { I18nProvider } from "@react-aria/i18n";
import { Info, WandSparkles } from "lucide-react";
import { lazy, useRef, useState, type FormEvent } from "react";

import { createLink } from "../actions/create-link";
import { checkRecaptcha } from "../actions/check-recaptcha";

const InputOptions = lazy( () => import( "./input-options" ) );
const CheckboxOptions = lazy( () => import( "./checkbox-options" ) );

export default function FormContainer()
{
	// Déclaration des variables d'état.
	const router = useRouter();
	const submitButton = useRef<HTMLButtonElement | null>( null );
	const [ stepName, setStepName ] = useState( "Récupération du jeton reCAPTCHA..." );
	const [ isLoading, setIsLoading ] = useState( false );

	// Récupération du jeton d'authentification reCAPTCHA.
	const getRecaptcha = async () =>
	{
		// Vérification de l'activation des vérifications reCAPTCHA.
		if ( process.env.NEXT_PUBLIC_RECAPTCHA_ENABLED !== "true" )
		{
			return "";
		}

		// Vérification du fonctionnement du service reCAPTCHA dans
		//  le navigateur de l'utilisateur.
		if ( typeof window.grecaptcha === "undefined" )
		{
			return "";
		}

		// Création d'une promesse pour gérer le chargement des services
		//  de Google reCAPTCHA.
		return new Promise( ( resolve ) =>
		{
			// Attente de la disponibilité des services de Google reCAPTCHA.
			window.grecaptcha.ready( async () =>
			{
				// Récupération du jeton d'authentification reCAPTCHA
				//  auprès des serveurs de Google.
				const token = await window.grecaptcha.execute(
					process.env.NEXT_PUBLIC_RECAPTCHA_PUBLIC_KEY,
					{ action: "submit" }
				);

				return resolve( token );
			} );
		} );
	};

	// Lance une animation de confettis lors de la soumission du formulaire.
	//  Source : https://github.com/nextui-org/nextui/blob/1485eca48fce8a0acc42fe40590b828c1a90ff48/apps/docs/components/demos/custom-button.tsx#L11-L36
	const throwConfetti = async () =>
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
	};

	// Réinitialisation de l'état du formulaire.
	const resetFormState = () =>
	{
		setIsLoading( false );
		setStepName( "Récupération d'un jeton reCAPTCHA..." );
	};

	// Requête HTTP de création d'un nouveau raccourci
	//  auprès du back-end PHP.
	const onSubmitForm = async ( event: FormEvent<HTMLFormElement> ) =>
	{
		// Affichage de l'état de chargement.
		event.preventDefault();

		setIsLoading( true );

		// Récupération du jeton reCAPTCHA et vérification de sa validité.
		const data = new FormData( event.currentTarget );
		const token = ( await getRecaptcha() ) as string|undefined;

		setStepName( "Vérification du jeton reCAPTCHA..." );

		const recaptchaResponse = await checkRecaptcha( token );

		if ( !recaptchaResponse.state )
		{
			alert( recaptchaResponse.message );
			resetFormState();
			return;
		}

		// Requête de création d'un nouveau raccourci.
		setStepName( "Requête de création du raccourci..." );

		const createState = await createLink( data );

		if ( !createState.state )
		{
			alert( createState.message );
			return;
		}

		// Lancement des confettis et redirection.
		await throwConfetti();

		setStepName( "Redirection vers le récapitulatif..." );

		router.push( `/dashboard/${ createState.data }` );
	};

	// Affichage du rendu HTML du composant.
	return (
		<Card
			as="section"
			className="bg-white dark:bg-default-100/30"
			isBlurred
			isFooterBlurred
		>
			{/* Corps du formulaire */}
			<Form onSubmit={onSubmitForm} validationBehavior="native">
				<CardHeader
					as="header"
					className="gap-3 bg-primary p-4 text-white"
				>
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
						type="submit"
						size="lg"
						color="success"
						variant="shadow"
						isLoading={isLoading}
						startContent={isLoading ? null : <WandSparkles />}
					>
						Créer un nouveau raccourci
					</Button>

					{/* Barre de progression */}
					{isLoading && (
						<Progress
							size="sm"
							label={stepName}
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
			</Form>
		</Card>
	);
}