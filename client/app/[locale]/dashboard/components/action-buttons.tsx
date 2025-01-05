//
// Composant des boutons d'action.
//

"use client";

import { useRouter } from "next/navigation";
import { showPreferences } from "vanilla-cookieconsent";
import { useEffect, useState } from "react";
import { ButtonGroup, Button, Link } from "@nextui-org/react";
import { Cookie, HandHelping, House, SunMoon } from "lucide-react";

export default function ActionButtons()
{
	// Déclaration des variables d'état.
	const router = useRouter();
	const [ theme, setTheme ] = useState( "N/A" );

	// Mise à jour du thème de l'interface utilisateur.
	const toggleTheme = () =>
	{
		const element = document.documentElement;
		const target = theme === "dark" ? "light" : "dark";

		element.classList.remove( "light", "dark" );
		element.classList.add( target );
		element.classList.toggle( "cc--darkmode" );
		element.style.colorScheme = target;

		setTheme( target );
	};

	// Détection du thème de l'interface utilisateur.
	useEffect( () =>
	{
		const element = document.documentElement;

		setTheme( element.style.colorScheme );
	}, [ theme ] );

	// Affichage du rendu HTML du composant.
	return (
		<ButtonGroup className="mb-8">
			<Button
				as={Link}
				variant="flat"
				onPress={() => router.push( "/" )}
				aria-label="Retour à l'accueil"
				startContent={<House />}
			>
				<span className="hidden md:inline">
					Retour à l&lsquo;accueil
				</span>
			</Button>

			<Button
				variant="flat"
				onPress={() => showPreferences()}
				aria-label="Gestion des cookies"
				startContent={<Cookie />}
			>
				<span className="hidden md:inline">Gestion des cookies</span>
			</Button>

			<Button
				variant="flat"
				onPress={toggleTheme}
				isDisabled={theme === "N/A"}
				aria-label={`Basculer en thème ${ theme === "dark" ? "clair" : "sombre" }`}
				startContent={<SunMoon />}
			>
				<span className="hidden md:inline">
					Basculer en thème {theme === "dark" ? "clair" : "sombre"}
				</span>
			</Button>

			<Button
				as={Link}
				onPress={() => window.open(
					"https://github.com/FlorianLeChat/Raven-Url-Shortener",
					"_blank",
					"noopener noreferrer"
				)}
				variant="flat"
				aria-label="Soutenir le projet"
				startContent={<HandHelping />}
				showAnchorIcon
			>
				<span className="hidden md:inline">Soutenir le projet</span>
			</Button>
		</ButtonGroup>
	);
}