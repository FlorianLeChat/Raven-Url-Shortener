//
// Composant d'une carte de fonctionnalité.
//

"use client";

import type { ReactNode } from "react";
import { Card, CardHeader, CardBody } from "@nextui-org/react";

export default function FeatureCard( {
	title,
	description,
	icon
}: {
	title: string;
	description: string;
	icon: ReactNode;
} )
{
	// Affichage du rendu HTML du composant.
	return (
		<Card
			isBlurred
			className="dark:bg-default-400/10 border-transparent bg-white backdrop-blur-lg backdrop-saturate-[1.8]"
		>
			<CardHeader className="gap-2 pb-0">
				<div className="text-primary-500 rounded-full p-2 dark:bg-transparent">
					{icon}
				</div>

				<p className="text-base font-semibold">{title}</p>
			</CardHeader>

			<CardBody className="pt-1">
				<p className="text-default-500 text-base font-normal">
					{description}
				</p>
			</CardBody>
		</Card>
	);
}