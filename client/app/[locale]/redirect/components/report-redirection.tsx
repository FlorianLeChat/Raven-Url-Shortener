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
import { useTranslations } from "next-intl";
import type { ErrorProperties } from "@/interfaces/ErrorProperties";
import { solveCaptchaChallenge } from "@/utilities/captcha";
import { useState, type FormEvent } from "react";
import { Flag, Mail, OctagonAlert, Send } from "lucide-react";

/**
 * Button opening the form that reports the shortened link to the administrators.
 */
export default function ReportRedirection( { id }: Readonly<{ id: string }> )
{
	const messages = useTranslations();
	const [ isLoading, setIsLoading ] = useState( false );
	const { isOpen, onOpen, onClose, onOpenChange } = useDisclosure();
	const [ isConsentChecked, setIsConsentChecked ] = useState( false );
	const [ isConsentRequired, setIsConsentRequired ] = useState( false );

	/**
	 * Sends the report to the API without its empty fields. An email address whose domain cannot
	 *  receive mail gets its own message, so the visitor can fix it or leave it out.
	 */
	const reportLink = async ( data: FormData ) =>
	{
		const body = new FormData();

		data.forEach( ( value, key ) =>
		{
			if ( value )
			{
				body.append( key, value );
			}
		} );

		console.log( "Form data before sending to the server." );
		console.table( Array.from( body.entries() ) );

		try
		{
			const linkId = data.get( "id" );
			const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/v1/link/${ linkId }/report`, {
				body,
				method: "POST"
			} );

			const json = ( await response.json() ) as ErrorProperties;

			console.log( "Short link reporting response." );
			console.table( json );

			if ( response.ok )
			{
				return { state: true };
			}

			const isCaptchaError = json.errors?.captcha !== undefined;
			const isUndeliverableEmail = json.errors?.email?.some( ( error ) => error.code === "UNDELIVERABLE_ERROR" );
			const emailErrorKey = isUndeliverableEmail ? "errors.report.undeliverable_email" : "errors.report.send_failed";
			const errorKey = isCaptchaError ? "errors.captcha.check_failed" : emailErrorKey;

			return {
				state: false,
				message: messages( errorKey )
			};
		}
		catch ( error )
		{
			console.log( "An error occurred while reporting the short link." );
			console.table( error );

			return {
				state: false,
				message: messages( "errors.generic_unknown" )
			};
		}
	};

	/**
	 * Solves the captcha challenge, then files the report and tells the result in a toast.
	 */
	const onSubmitForm = async ( event: FormEvent<HTMLFormElement> ) =>
	{
		event.preventDefault();

		const formData = new FormData( event.currentTarget );
		formData.set( "id", id );

		setIsLoading( true );

		const captchaPayload = await solveCaptchaChallenge();

		if ( captchaPayload )
		{
			formData.set( "captcha", captchaPayload );
		}

		if ( captchaPayload === null )
		{
			addToast( {
				color: "danger",
				title: messages( "errors.check_error" ),
				description: messages( "errors.captcha.check_failed" )
			} );

			onClose();
			setIsLoading( false );

			return;
		}

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

		addToast( {
			color: "success",
			title: messages( "redirect.report.success_title" ),
			description: messages( "redirect.report.success_description" )
		} );

		onClose();
		setIsLoading( false );
	};

	return (
		<>
			<Modal
				isOpen={isOpen}
				backdrop="blur"
				placement="top-center"
				onOpenChange={onOpenChange}
			>
				<ModalContent>
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

							<Checkbox
								isInvalid={isConsentRequired && !isConsentChecked}
								isDisabled={isLoading || !isConsentRequired}
								onValueChange={( value ) => setIsConsentChecked( value )}
							>
								{messages( "redirect.report.consent_label" )}
							</Checkbox>
						</ModalBody>

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