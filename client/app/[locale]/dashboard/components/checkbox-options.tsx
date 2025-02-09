//
// Composant des options de sécurité du formulaire.
//

"use client";

import { useTranslations } from "next-intl";
import { ChartLine, Shield, Zap } from "lucide-react";
import { Chip, Alert, Checkbox, Accordion, AccordionItem } from "@heroui/react";

export default function CheckboxOptions()
{
	// Déclaration des variables d'état.
	const messages = useTranslations( "dashboard.categories" );

	// Affichage du rendu HTML du composant.
	return (
		<Accordion
			as="ul"
			variant="splitted"
			className="overflow-y-auto p-1 lg:ml-4 lg:max-h-[382px] lg:w-1/2"
			isDisabled={process.env.NEXT_PUBLIC_ENV === "production"}
			keepContentMounted
		>
			{/* Protection et sécurité */}
			<AccordionItem
				key="1"
				title={messages( "security.title" )}
				subtitle={messages.rich( "security.description", {
					danger: ( text ) => (
						<span className="text-danger">{text}</span>
					)
				} )}
				className="bg-content2/50 shadow-md"
				aria-label={messages( "security.title" )}
				startContent={<Shield className="mr-1 text-danger" />}
			>
				<Alert
					color="danger"
					title={<strong>{messages( "security.warning_label" )}</strong>}
					description={messages( "security.warning_description" )}
				/>

				{/* Sécurisation par mot de passe */}
				<Checkbox
					name="password-protection"
					className="w-100 m-0 mt-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label={messages( "security.options.password.label" )}
				>
					<div className="flex justify-between gap-2">
						<p>
							{messages( "security.options.password.label" )}
							<br />
							<small className="inline-block text-tiny leading-5 text-default-500">
								{messages( "security.options.password.description" )}
							</small>
						</p>

						<Chip color="success" size="sm" variant="flat">
							{messages( "recommended_label" )}
						</Chip>
					</div>
				</Checkbox>

				{/* Sécurisation via Google reCAPTCHA */}
				<Checkbox
					name="captcha-protection"
					className="w-100 m-0 my-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label={messages( "security.options.recaptcha.label" )}
					isDisabled={process.env.NEXT_PUBLIC_RECAPTCHA_ENABLED !== "true"}
				>
					<div className="flex justify-between gap-2">
						<p>
							{messages( "security.options.recaptcha.label" )}
							<br />
							<small className="inline-block text-tiny leading-5 text-default-500">
								{messages( "security.options.recaptcha.description" )}
							</small>
						</p>

						<Chip color="success" size="sm" variant="flat">
							{messages( "recommended_label" )}
						</Chip>
					</div>
				</Checkbox>

				{/* Accès via un serveur proxy */}
				<Checkbox
					name="proxy-server"
					className="w-100 m-0 mb-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label={messages( "security.options.proxy.label" )}
				>
					<p>{messages( "security.options.proxy.label" )}</p>

					<small className="inline-block text-tiny leading-5 text-default-500">
						{messages( "security.options.proxy.description" )}
					</small>
				</Checkbox>

				{/* Gestion via API */}
				<Checkbox
					name="api-management"
					className="w-100 m-0 mb-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label={messages( "security.options.api.label" )}
				>
					<div className="flex justify-between gap-2">
						<p>
							{messages( "security.options.api.label" )}
							<br />
							<small className="inline-block text-tiny leading-5 text-default-500">
								{messages( "security.options.api.description" )}
							</small>
						</p>

						<Chip color="secondary" size="sm" variant="flat">
							{messages( "experimental_label" )}
						</Chip>
					</div>
				</Checkbox>
			</AccordionItem>

			{/* Suivi et statistiques */}
			<AccordionItem
				key="2"
				title={messages( "statistics.title" )}
				subtitle={messages.rich( "statistics.description", {
					success: ( text ) => (
						<span className="text-success">{text}</span>
					)
				} )}
				className="bg-content2/50 shadow-md"
				aria-label={messages( "statistics.title" )}
				startContent={<ChartLine className="mr-1 text-success" />}
			>
				<Alert
					color="warning"
					title={<strong>{messages( "statistics.warning_label" )}</strong>}
					description={messages( "statistics.warning_description" )}
				/>

				{/* Comptage du nombre d'accès */}
				<Checkbox
					name="clicks-views"
					className="w-100 m-0 mt-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label={messages( "statistics.options.access_count.label" )}
				>
					<p>{messages( "statistics.options.access_count.label" )}</p>

					<small className="inline-block text-tiny leading-5 text-default-500">
						{messages( "statistics.options.access_count.description" )}
					</small>
				</Checkbox>

				{/* Récupération des données des visiteurs */}
				<Checkbox
					name="client-data"
					className="w-100 m-0 my-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label={messages( "statistics.options.visitor_data.label" )}
				>
					<p>{messages( "statistics.options.visitor_data.label" )}</p>

					<small className="inline-block text-tiny leading-5 text-default-500">
						{messages( "statistics.options.visitor_data.description" )}
					</small>
				</Checkbox>
			</AccordionItem>

			{/* Performance et optimisation */}
			<AccordionItem
				key="3"
				title={messages( "performance.title" )}
				subtitle={messages.rich( "performance.description", {
					warning: ( text ) => (
						<span className="text-warning">{text}</span>
					)
				} )}
				className="bg-content2/50 shadow-md"
				aria-label={messages( "performance.title" )}
				startContent={<Zap className="mr-1 text-warning" />}
			>
				{/* Mise en cache de la page */}
				<Checkbox
					name="cache-page"
					className="w-100 m-0 mb-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label={messages( "performance.options.cache.label" )}
				>
					<div className="flex justify-between gap-2">
						<p>
							{messages( "performance.options.cache.label" )}
							<br />
							<small className="inline-block text-tiny leading-5 text-default-500">
								{messages( "performance.options.cache.description" )}
							</small>
						</p>

						<Chip color="secondary" size="sm" variant="flat">
							{messages( "experimental_label" )}
						</Chip>
					</div>
				</Checkbox>
			</AccordionItem>
		</Accordion>
	);
}