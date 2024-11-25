//
// Route vers la page principale du site.
//  Source : https://nextjs.org/docs/app/building-your-application/routing/pages-and-layouts#pages
//

// Importation des dépendances.
import { lazy } from "react";

// Importation de la configuration.
import { features } from "@/config/features";

// Importation des composants.
const Header = lazy( () => import( "./components/Header" ) );
const Footer = lazy( () => import( "./components/Footer" ) );
const FeatureCard = lazy( () => import( "./components/FeatureCard" ) );
const GatewayButton = lazy( () => import( "./components/GatewayButton" ) );

export default function Home()
{
	return (
		<>
			{/* En-tête de la page */}
			<Header />

			{/* Contenu de la page */}
			<main className="container mx-auto max-w-[1440px] p-4 !pt-0 md:p-8">
				{/* Présentation des fonctionnalités */}
				<section className="mb-4 md:mb-8">
					<header className="mb-4 md:mb-8">
						<h1 className="inline text-2xl font-semibold tracking-tight lg:text-3xl">
							Vous allez&nbsp;
						</h1>

						<h1 className="inline bg-gradient-to-b from-[#FF72E1] to-[#F54C7A] bg-clip-text text-2xl font-semibold tracking-tight text-transparent lg:text-3xl">
							adorer&nbsp;
						</h1>

						<h1 className="inline text-2xl font-semibold tracking-tight lg:text-3xl">
							notre service.
						</h1>
					</header>

					<article className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
						{features.map( ( { id, title, description, icon } ) => (
							<FeatureCard
								key={id}
								icon={icon}
								title={title}
								description={description}
							/>
						) )}
					</article>
				</section>

				{/* Redirection vers le formulaire de création */}
				<section>
					<header className="mb-4 md:mb-6">
						<h1 className="inline text-2xl font-semibold tracking-tight lg:text-3xl">
							Prêt à&nbsp;
						</h1>

						<h1 className="inline bg-gradient-to-b from-[#6FEE8D] to-[#17c964] bg-clip-text text-2xl font-semibold tracking-tight text-transparent lg:text-3xl">
							commencer&nbsp;
						</h1>

						<h1 className="inline text-2xl font-semibold tracking-tight lg:text-3xl">
							?
						</h1>
					</header>

					<GatewayButton />
				</section>
			</main>

			{/* Pied de page */}
			<Footer />
		</>
	);
}