//
// Composant des options de sécurité du formulaire.
//

"use client";

import { Chip,
	Alert,
	Checkbox,
	Accordion,
	AccordionItem } from "@nextui-org/react";
import { ChartLine, Shield, Zap } from "lucide-react";

export default function CheckboxOptions()
{
	// Affichage du rendu HTML du composant.
	return (
		<Accordion
			as="ul"
			variant="splitted"
			className="max-h-[382px] overflow-y-auto p-1 md:ml-4 lg:w-1/2"
			isDisabled={process.env.NEXT_PUBLIC_ENV === "production"}
			keepContentMounted
		>
			{/* Protection et sécurité */}
			<AccordionItem
				key="1"
				title="Protection et sécurité"
				subtitle={(
					<>
						Ces paramètres vous permettent de sécuriser
						l&lsquo;accès à votre lien pour{" "}
						<span className="text-danger">vous</span> et{" "}
						<span className="text-danger">vos visiteurs</span>.
					</>
				)}
				className="bg-content2/50 shadow-md"
				aria-label="Protection et sécurité"
				startContent={<Shield className="mr-1 text-danger" />}
			>
				<Alert
					color="danger"
					title={<strong>Attention requise</strong>}
					description="Certains paramètres peuvent rendre votre lien inaccessible. Veuillez les configurer avec précaution."
				/>

				{/* Sécurisation par mot de passe */}
				<Checkbox
					value="password-protection"
					className="w-100 m-0 mt-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label="Activer la protection par mot de passe"
				>
					<div className="flex justify-between gap-2">
						<p>
							Activer la protection par mot de passe
							<br />
							<small className="inline-block text-tiny leading-5 text-default-500">
								Les visiteurs devront saisir un mot de passe que
								vous aurez défini pour accéder à votre lien.
								Cette mesure permet de sécuriser votre lien et
								de prévenir tout accès non autorisé.
							</small>
						</p>

						<Chip color="success" size="sm" variant="flat">
							Recommandé
						</Chip>
					</div>
				</Checkbox>

				{/* Sécurisation via Google reCAPTCHA */}
				<Checkbox
					value="captcha-protection"
					className="w-100 m-0 my-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label="Activer la protection via Google reCAPTCHA"
				>
					<div className="flex justify-between gap-2">
						<p>
							Activer la protection via Google reCAPTCHA
							<br />
							<small className="inline-block text-tiny leading-5 text-default-500">
								Les visiteurs devront résoudre un défi reCAPTCHA
								pour accéder à votre lien. Cette mesure aide à
								protéger votre lien contre les robots et les
								logiciels malveillants, garantissant ainsi une
								sécurité accrue.
							</small>
						</p>

						<Chip color="success" size="sm" variant="flat">
							Recommandé
						</Chip>
					</div>
				</Checkbox>

				{/* Accès via un serveur proxy */}
				<Checkbox
					value="proxy-server"
					className="w-100 m-0 mb-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label="Compter le nombre de clics et de vues"
				>
					<p>Acheminer via un serveur mandataire (proxy)</p>

					<small className="inline-block text-tiny leading-5 text-default-500">
						L&lsquo;accès à votre lien passera par un serveur
						passerelle, ce qui permettra de contourner les
						restrictions de certains réseaux tout en protégeant
						votre adresse IP contre le suivi de votre activité en
						ligne.
					</small>
				</Checkbox>

				{/* Gestion via API */}
				<Checkbox
					value="api-management"
					className="w-100 m-0 mb-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label="Gérer le lien via une API"
				>
					<div className="flex justify-between gap-2">
						<p>
							Activer la gestion via une API
							<br />
							<small className="inline-block text-tiny leading-5 text-default-500">
								La gestion de votre lien pourra être effectuée à
								distance à l&lsquo;aide d&lsquo;une interface de
								programmation d&lsquo;applications (API), vous
								permettant ainsi de contrôler son accès, ses
								paramètres et d&lsquo;accéder à ses
								statistiques.
							</small>
						</p>

						<Chip color="secondary" size="sm" variant="flat">
							Expérimental
						</Chip>
					</div>
				</Checkbox>
			</AccordionItem>

			{/* Suivi et statistiques */}
			<AccordionItem
				key="2"
				title="Suivi et statistiques"
				subtitle={(
					<>
						Ces paramètres permettent de{" "}
						<span className="text-success">suivre</span> les
						performances de votre lien et d&lsquo;en{" "}
						<span className="text-success">analyser</span> son
						audience.
					</>
				)}
				className="bg-content2/50 shadow-md"
				aria-label="Suivi et statistiques"
				startContent={<ChartLine className="mr-1 text-success" />}
			>
				<Alert
					color="warning"
					title={<strong>Note importante</strong>}
					description="En fonction de votre position géographique, certains paramètres peuvent déclencher l'acceptation de consentements supplémentaires lors de l'accès au lien par vos visiteurs."
				/>

				{/* Comptage du nombre d'accès */}
				<Checkbox
					value="clicks-views"
					className="w-100 m-0 mt-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label="Compter le nombre de clics et de vues"
				>
					<p>Compter le nombre d&lsquo;accès</p>

					<small className="inline-block text-tiny leading-5 text-default-500">
						Le nombre d&lsquo;accès sera enregistré et affiché dans
						les statistiques de gestion de votre lien, vous
						permettant ainsi de suivre sa popularité et son
						audience.
					</small>
				</Checkbox>

				{/* Récupération des données des visiteurs */}
				<Checkbox
					value="client-data"
					className="w-100 m-0 my-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label="Récupérer les données des visiteurs"
				>
					<p>Récupérer les données des visiteurs</p>

					<small className="inline-block text-tiny leading-5 text-default-500">
						Les données des navigateurs des visiteurs seront
						collectées et affichées dans les statistiques de gestion
						de votre lien, vous offrant une analyse détaillée de
						leur activité.
					</small>
				</Checkbox>
			</AccordionItem>

			{/* Performance et optimisation */}
			<AccordionItem
				key="3"
				title="Performance et optimisation"
				subtitle={(
					<>
						Ces paramètres permettent de{" "}
						<span className="text-warning">booster</span> la vitesse
						de chargement de votre lien et{" "}
						<span className="text-warning">d&lsquo;optimiser</span>{" "}
						son rendement.
					</>
				)}
				className="bg-content2/50 shadow-md"
				aria-label="Performance et optimisation"
				startContent={<Zap className="mr-1 text-warning" />}
			>
				{/* Mise en cache de la page */}
				<Checkbox
					value="cache-page"
					className="w-100 m-0 mb-2 inline-flex cursor-pointer items-center justify-start gap-2 rounded-lg border-2 border-transparent p-4 hover:bg-content2 data-[selected=true]:border-primary"
					aria-label="Mettre en cache la page"
				>
					<div className="flex justify-between gap-2">
						<p>
							Mise en cache de la page
							<br />
							<small className="inline-block text-tiny leading-5 text-default-500">
								Lors de la création de votre lien, la page cible
								sera mise en cache et sera servie pour les
								visiteurs, ce qui permettra d&lsquo;accélérer le
								chargement de la page.
							</small>
						</p>

						<Chip color="secondary" size="sm" variant="flat">
							Expérimental
						</Chip>
					</div>
				</Checkbox>
			</AccordionItem>
		</Accordion>
	);
}