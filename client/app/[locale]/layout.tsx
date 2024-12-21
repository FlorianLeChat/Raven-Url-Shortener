//
// Structure HTML générale des pages du site.
//  Source : https://nextjs.org/docs/app/building-your-application/routing/pages-and-layouts#root-layout-required
//

// Importation du normalisateur TypeScript.
import "@total-typescript/ts-reset";

// Importation des dépendances.
import { Inter } from "next/font/google";
import { Suspense, type ReactNode } from "react";

// Importation des fonctions utilitaires.
import { NextUIProvider } from "@/utilities/next-ui";

// Création des polices de caractères.
const inter = Inter( {
	subsets: [ "latin" ],
	display: "swap"
} );

export default function Layout( { children }: Readonly<{ children: ReactNode }> )
{
	return (
		<html
			lang="fr"
			className={`text-foreground light:bg-[whitesmoke] ${ inter.className }`}
			suppressHydrationWarning
		>
			{/* En-tête de la page */}
			<head>
				{/* Mise à jour de l'apparence */}
				<script
					dangerouslySetInnerHTML={{
						__html: `
							// Application du thème préféré par le navigateur.
							const element = document.documentElement;
							const target = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";

							element.classList.remove("light", "dark");
							element.classList.add(target);
							element.style.colorScheme = target;

							if (target === "dark") {
								element.classList.add("cc--darkmode");
							}
						`
					}}
				/>
			</head>

			{/* Corps de la page */}
			<body>
				{/* Vidéo en arrière-plan */}
				<video
					loop
					muted
					autoPlay
					className="fixed -z-10 hidden size-full object-none opacity-10 dark:block"
				>
					<source
						src={`${ process.env.__NEXT_ROUTER_BASEPATH }/assets/videos/background.mp4`}
						type="video/mp4"
					/>
				</video>

				{/* Écran de chargement de la page */}
				<Suspense>
					{/* Affichage de l'animation du logo vers le dépôt GitHub */}
					{/* Source : https://tholman.com/github-corners/ */}
					<a
						rel="noopener noreferrer"
						href="https://github.com/FlorianLeChat/Raven-Url-Shortener"
						title="GitHub"
						target="_blank"
						className="group fixed bottom-auto left-auto right-0 top-0 [clip-path:polygon(0_0,100%_0,100%_100%)] max-sm:hidden"
						aria-label="GitHub"
					>
						<svg
							width="80"
							height="80"
							viewBox="0 0 250 250"
							className="fill-primary text-white"
						>
							<path d="M0 0l115 115h15l12 27 108 108V0z" />
							<path
								d="M128 109c-15-9-9-19-9-19 3-7 2-11 2-11-1-7 3-2 3-2 4 5 2 11 2 11-3 10 5 15 9 16"
								className="origin-[130px_106px] fill-current max-md:motion-safe:animate-github md:motion-safe:group-hover:animate-github"
							/>
							<path
								d="M115 115s4 2 5 0l14-14c3-2 6-3 8-3-8-11-15-24 2-41 5-5 10-7 16-7 1-2 3-7 12-11 0 0
						5 3 7 16 4 2 8 5 12 9s7 8 9 12c14 3 17 7 17 7-4 8-9 11-11 11 0 6-2 11-7 16-16 16-30 10-41
						2 0 3-1 7-5 11l-12 11c-1 1 1 5 1 5z"
								className="fill-current"
							/>
						</svg>
					</a>

					{/* Utilisation de NextUI */}
					<NextUIProvider className="flex min-h-screen flex-col">
						{/* Composant enfant */}
						{children}
					</NextUIProvider>
				</Suspense>
			</body>
		</html>
	);
}