//
// Composant du bouton de signalement de la redirection.
//

"use client";

import { Form,
	Alert,
	Modal,
	Input,
	Button,
	Textarea,
	addToast,
	Checkbox,
	ModalBody,
	ModalFooter,
	ModalHeader,
	ModalContent,
	useDisclosure } from "@heroui/react";
import { getRecaptcha } from "@/utilities/recaptcha";
import { useTranslations } from "next-intl";
import { useState, type FormEvent } from "react";
import { Flag, Mail, OctagonAlert, Send } from "lucide-react";

import { reportLink } from "../actions/report-link";
import { checkRecaptcha } from "../../dashboard/actions/check-recaptcha";

export default function ReportRedirection( { id }: Readonly<{ id: string }> )
{
	// Déclaration des variables d'état.
	const messages = useTranslations();
	const [ isLoading, setIsLoading ] = useState( false );
	const { isOpen, onOpen, onClose, onOpenChange } = useDisclosure();
	const [ isConsentChecked, setIsConsentChecked ] = useState( false );
	const [ isConsentRequired, setIsConsentRequired ] = useState( false );

	// Requête HTTP de signalement d'un raccourci
	//  auprès du back-end PHP.
	const onSubmitForm = async ( event: FormEvent<HTMLFormElement> ) =>
	{
		// Activation de l'état de chargement.
		event.preventDefault();

		const formData = new FormData( event.currentTarget );
		formData.set( "id", id );

		setIsLoading( true );

		// Récupération du jeton reCAPTCHA et vérification de sa validité.
		const token = ( await getRecaptcha() ) as string | undefined;
		const recaptchaResponse = await checkRecaptcha( token );

		if ( !recaptchaResponse.state )
		{
			addToast( {
				color: "danger",
				title: messages( "errors.check_error" ),
				description: recaptchaResponse.message
			} );

			onClose();
			setIsLoading( false );

			return;
		}

		// Requête de création d'un nouveau signalement.
		const reportState = await reportLink( formData );

		if ( !reportState.state || "message" in reportState )
		{
			addToast( {
				color: "danger",
				title: messages( "errors.creation_error" ),
				description: reportState.message
			} );

			onClose();
			setIsLoading( false );

			return;
		}

		// Message de confirmation de la création du signalement.
		addToast( {
			color: "success",
			title: messages( "redirect.report.success_title" ),
			description: messages( "redirect.report.success_description" )
		} );

		onClose();
		setIsLoading( false );
	};

	// Affichage du rendu HTML du composant.
	return (
		<>
			<Modal
				isOpen={isOpen}
				backdrop="blur"
				placement="top-center"
				onOpenChange={onOpenChange}
			>
				<ModalContent>
					{/* En-tête du formulaire */}
					<ModalHeader className="flex items-center gap-2">
						<OctagonAlert />
						{messages( "redirect.report.default" )}
					</ModalHeader>

					<Form onSubmit={onSubmitForm} validationBehavior="native">
						<ModalBody>
							<Alert
								color="danger"
								title={<strong>{messages( "redirect.report.warning_label" )}</strong>}
								description={messages( "redirect.report.warning_description" )}
							/>

							{/* Description */}
							<Textarea
								size="lg"
								name="reason"
								label={messages( "redirect.report.reason_label" )}
								minRows={10}
								required
								minLength={10}
								maxLength={500}
								className="w-full"
								isRequired
								isDisabled={isLoading}
								isClearable
								description={messages( "redirect.report.reason_description" )}
								placeholder={messages( "redirect.report.reason_placeholder" )}
								labelPlacement="outside"
							/>

							{/* Adresse électronique */}
							<Input
								size="lg"
								type="email"
								name="email"
								label={messages( "redirect.report.email_label" )}
								isDisabled={isLoading}
								isClearable
								description={messages( "redirect.report.email_description" )}
								placeholder={messages( "redirect.report.email_placeholder" )}
								startContent={<Mail className="mr-1" />}
								onValueChange={( value ) => setIsConsentRequired( !!value )}
								labelPlacement="outside"
							/>

							{/* Consentement */}
							<Checkbox
								isInvalid={isConsentRequired && !isConsentChecked}
								isDisabled={isLoading || !isConsentRequired}
								onValueChange={( value ) => setIsConsentChecked( value )}
							>
								{messages( "redirect.report.consent_label" )}
							</Checkbox>
						</ModalBody>

						{/* Pied de page du formulaire */}
						<ModalFooter className="w-full">
							<Button
								type="submit"
								color="primary"
								isLoading={isLoading}
								startContent={isLoading ? null : <Send />}
							>
								{messages( "redirect.report.submit_button" )}
							</Button>
						</ModalFooter>
					</Form>
				</ModalContent>
			</Modal>

			{/* Bouton d'ouverture du formulaire */}
			<Button
				size="lg"
				color="danger"
				variant="bordered"
				onPress={() => onOpen()}
				isLoading={isLoading}
				aria-label={messages( "redirect.report.default" )}
				startContent={isLoading ? null : <Flag />}
			>
				{messages( "redirect.report.default" )}
			</Button>
		</>
	);
}