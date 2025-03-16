//
// Composant des boutons d'action.
//

"use client";

import { useRouter } from "next/navigation";
import { useTranslations } from "next-intl";
import { showPreferences } from "vanilla-cookieconsent";
import { useEffect, useState } from "react";
import { ButtonGroup, Button, Link } from "@heroui/react";
import { Cookie, HandHelping, House, Scale, SunMoon } from "lucide-react";

export default function ActionButtons()
{
	// Déclaration des variables d'état.
	const router = useRouter();
	const messages = useTranslations( "navigation" );
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
				className="max-sm:min-w-16"
				aria-label={messages( "home" )}
				startContent={<House />}
			>
				<span className="hidden lg:inline">{messages( "home" )}</span>
			</Button>

			<Button
				variant="flat"
				onPress={() => showPreferences()}
				className="max-sm:min-w-16"
				aria-label={messages( "cookies" )}
				startContent={<Cookie />}
			>
				<span className="hidden lg:inline">{messages( "cookies" )}</span>
			</Button>

			<Button
				variant="flat"
				onPress={toggleTheme}
				className="max-sm:min-w-16"
				isDisabled={theme === "N/A"}
				aria-label={messages(
					`${ theme === "dark" ? "light" : "dark" }_theme`
				)}
				startContent={<SunMoon />}
			>
				<span className="hidden lg:inline">
					{messages( `${ theme === "dark" ? "light" : "dark" }_theme` )}
				</span>
			</Button>

			<Button
				as={Link}
				onPress={() => router.push( "/legal" )}
				variant="flat"
				className="max-sm:min-w-16"
				aria-label={messages( "legal" )}
				startContent={<Scale />}
			>
				<span className="hidden lg:inline">{messages( "legal" )}</span>
			</Button>

			<Button
				as={Link}
				onPress={() => window.open(
					"https://github.com/FlorianLeChat/Raven-Url-Shortener",
					"_blank",
					"noopener noreferrer"
				)}
				variant="flat"
				className="max-sm:min-w-16"
				aria-label={messages( "support" )}
				startContent={<HandHelping />}
				showAnchorIcon
			>
				<span className="hidden lg:inline">{messages( "support" )}</span>
			</Button>
		</ButtonGroup>
	);
}