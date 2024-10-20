//
// Route vers la page principale du site.
//  Source : https://nextjs.org/docs/app/building-your-application/routing/pages-and-layouts#pages
//

// Importation des dépendances.
import { lazy } from "react";
import { Code, Eye, Lock, Palette, Smile, Zap } from "lucide-react";

// Importation des composants.
const Footer = lazy( () => import( "./components/Footer" ) );
const FeatureCard = lazy( () => import( "./components/FeatureCard" ) );
const GatewayButton = lazy( () => import( "./components/GatewayButton" ) );

// Liste des fonctionnalités.
const features = [
	{
		id: 1,
		title: "Sécurisé de bout en bout",
		description:
			"Toutes les données transmises entre votre navigateur et nos serveurs sont chiffrées pour garantir une sécurité maximale.",
		icon: <Lock />
	},
	{
		id: 2,
		title: "Interface ergonomique",
		description:
			"L'interface est conçue pour être simple et ergonomique pour tous les utilisateurs, même pour vos grands-parents.",
		icon: <Smile />
	},
	{
		id: 3,
		title: "Respect de la vie privée",
		description:
			"Vos données sont stockées sur des serveurs basés en Europe conformément au RGPD pour garantir une intégrité et une confidentialité totale.",
		icon: <Eye />
	},
	{
		id: 4,
		title: "Haute performance",
		description:
			"Nos serveurs sont prévus pour garantir une performance optimale et une disponibilité maximale pour tous les utilisateurs.",
		icon: <Zap />
	},
	{
		id: 5,
		title: "Personnalisation multiple",
		description:
			"Notre service propose une grande variétés d'options de personnalisation lors de la création des raccourcis vers vos liens Internet.",
		icon: <Palette />
	},
	{
		id: 6,
		title: "Ouvert aux développeurs",
		description:
			"Notre API est ouverte à tous les développeurs qui souhaitent intégrer notre service dans leurs applications.",
		icon: <Code />
	}
];

export default function Home()
{
	return (
		<>
			{/* En-tête de la page */}
			<header className="container mx-auto max-w-[1440px] p-4 md:p-8">
				<h1 className="inline text-4xl font-semibold tracking-tight lg:text-5xl">
					Bienvenue sur
				</h1>

				<h1 className="mt-2 block bg-gradient-to-b from-[#5EA2EF] to-[#0072F5] bg-clip-text text-4xl font-semibold tracking-tight text-transparent lg:text-5xl">
					Raven Url Shortener.
				</h1>

				<p className="text-default-500 my-2 block w-full max-w-full text-lg font-normal md:w-1/2 lg:text-xl">
					Un raccourcisseur de liens Internet simple, sécurisé et
					entièrement personnalisable, conçu pour protéger votre
					confidentialité.
				</p>
			</header>

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