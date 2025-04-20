//
// Importation statique des règles CSS transformées par Tailwind CSS.
//  Note : cette page ne devrait pas exister mais NextJS ne permet pas
//   de charger les règles CSS globales depuis des routes dynamiques...
//

import "./layout.css";
import "vanilla-cookieconsent/dist/cookieconsent.css";

import { Inter } from "next/font/google";
import type { ReactNode } from "react";

const inter = Inter( {
	subsets: [ "latin" ],
	display: "swap"
} );

export default function Layout( { children }: { children: ReactNode } )
{
	// Les polices de caractères sont chargées mais ne sont pas utilisées
	//  dans ce fichier, elles sont ainsi utilisées dans les routes dynamiques
	//  en appelant de nouveau ces mêmes fonctions car elles sont désormais en cache.
	console.log( inter.className );

	return children;
}