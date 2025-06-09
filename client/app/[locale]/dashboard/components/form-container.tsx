//
// Composant du conteneur général du formulaire.
//

"use client";

import { Form,
	Link,
	User,
	Card,
	Button,
	addToast,
	Progress,
	CardBody,
	CardHeader,
	CardFooter } from "@heroui/react";
import { useRouter } from "next/navigation";
import { I18nProvider } from "@react-aria/i18n";
import { useTranslations } from "next-intl";
import { Info, WandSparkles } from "lucide-react";
import type { LinkProperties } from "@/interfaces/LinkProperties";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";
import { solveCaptchaChallenge } from "@/utilities/captcha";
import { verifyCaptchaResolution } from "../actions/check-captcha";
import { lazy, useContext, useRef, useState, type FormEvent } from "react";

import { ServerContext } from "@/components/provider-server";

const InputOptions = lazy( () => import( "./input-options" ) );
const LegalConsent = lazy( () => import( "@/components/consent-legal" ) );
const CheckboxOptions = lazy( () => import( "./checkbox-options" ) );

type CreateLinkResponse = LinkProperties | ErrorProperties;

export default function FormContainer()
{
	// Déclaration des variables d'état.
	const router = useRouter();
	const messages = useTranslations();
	const serverData = useContext( ServerContext );
	const submitButton = useRef<HTMLButtonElement | null>( null );
	const [ stepName, setStepName ] = useState( messages( "dashboard.steps.generate_challenge" ) );
	const [ isLoading, setIsLoading ] = useState( false );

	// Lance une animation de confettis lors de la soumission du formulaire.
	//  Source : https://github.com/heroui-inc/heroui/blob/1485eca48fce8a0acc42fe40590b828c1a90ff48/apps/docs/components/demos/custom-button.tsx#L11-L36
	const throwConfettis = async () =>
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
		setStepName( messages( "dashboard.steps.generate_challenge" ) );
	};

	// Création d'un nouveau raccourci auprès du back-end PHP.
	const createLink = async ( data: FormData ) =>
	{
		const body = new FormData();

		data.forEach( ( value, key ) =>
		{
			// Suppression des champs vides.
			if ( value )
			{
				body.append( key, value );
			}
		} );

		console.log( "Form data before sending to the server." );
		console.table( Array.from( body.entries() ) );

		try
		{
			const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/api/v1/link`, {
				body,
				method: "POST"
			} );

			const json = ( await response.json() ) as CreateLinkResponse;

			console.log( "Short link creation response." );
			console.table( json );

			if ( response.ok && "id" in json )
			{
				return {
					state: true,
					data: json
				};
			}

			return {
				state: false,
				message: messages( "errors.link.creation_failed" )
			};
		}
		catch ( error )
		{
			console.log( "An error occurred while creating the short link." );
			console.table( error );

			return {
				state: false,
				message: messages( "errors.generic_unknown" )
			};
		}
	};

	// Requête HTTP de création d'un nouveau raccourci
	//  auprès du back-end PHP.
	const onSubmitForm = async ( event: FormEvent<HTMLFormElement> ) =>
	{
		// Vérification du consentement de l'utilisateur.
		event.preventDefault();

		if ( !localStorage.getItem( "NEXT_CONSENT" ) )
		{
			addToast( {
				color: "warning",
				title: messages( "errors.consent_missing" ),
				description: messages( "errors.consent_required" )
			} );

			return;
		}

		setIsLoading( true );

		// Récupération et résolution d'un défi CAPTCHA.
		setStepName( messages( "dashboard.steps.solve_challenge" ) );

		const data = new FormData( event.currentTarget );
		const captchaPayload = await solveCaptchaChallenge();
		const isValidCaptcha = await verifyCaptchaResolution( captchaPayload );

		if ( !isValidCaptcha )
		{
			addToast( {
				color: "danger",
				title: messages( "errors.check_error" ),
				description: messages( "errors.captcha.check_failed" )
			} );

			resetFormState();
			return;
		}

		// Requête de création d'un nouveau raccourci.
		setStepName( messages( "dashboard.steps.creation_request" ) );

		const createState = await createLink( data );

		if ( !createState.state || "message" in createState )
		{
			addToast( {
				color: "danger",
				title: messages( "errors.creation_error" ),
				description: createState.message
			} );

			resetFormState();
			return;
		}

		// Lancement des confettis et redirection.
		await throwConfettis();

		setStepName( messages( "dashboard.steps.summary_redirect" ) );

		router.push( `/dashboard/${ createState.data.id }` );
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
					{messages( "dashboard.hint" )}
				</CardHeader>

				<CardBody className="p-4 max-md:gap-6 lg:flex-row">
					{/* Utilisation de i18n */}
					<I18nProvider locale={serverData?.locale}>
						{/* Options de saisie */}
						<InputOptions />

						{/* Options additionnelles */}
						<CheckboxOptions />
					</I18nProvider>
				</CardBody>

				<CardFooter
					as="footer"
					className="justify-between gap-6 bg-content2/50 max-lg:flex-col"
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
						{messages( "dashboard.submit_button" )}
					</Button>

					{/* Consentement de l'utilisateur */}
					<LegalConsent />

					{/* Barre de progression */}
					{isLoading && (
						<Progress
							size="sm"
							label={stepName}
							className="max-md:max-w-xs md:max-w-md"
							isIndeterminate
						/>
					)}

					{/* Carte du créateur */}
					<div className="mr-1 flex items-center gap-2">
						{messages( "footer.made_with_love" )}
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