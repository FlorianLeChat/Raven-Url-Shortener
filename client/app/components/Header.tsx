//
// Composant de l'en-tête du site.
//
export default function Header()
{
	// Affichage du rendu HTML du composant.
	return (
		<header className="container mx-auto max-w-[1440px] p-4 md:p-8">
			<h1 className="inline text-4xl font-semibold tracking-tight lg:text-5xl">
				Vous êtes sur
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
	);
}