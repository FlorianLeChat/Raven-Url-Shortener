//
// Composant des actions disponibles dans le récapitulatif.
//

"use client";

import { Button, Tooltip } from "@heroui/react";
import { Info,
	Trash2,
	ChartLine,
	RefreshCw,
	LayoutDashboard } from "lucide-react";

export default function SummaryActions()
{
	// Déclaration des constantes.
	const isProduction = process.env.NEXT_PUBLIC_ENV === "production";

	// Affichage du rendu HTML du composant.
	return (
		<ul className="flex flex-col gap-3">
			<li>
				<h3 className="inline-flex items-center text-xl font-semibold">
					Actions disponibles
					<Tooltip content="Ces options sont en cours de développement. Elles seront disponibles prochainement.">
						<Info className="ml-2 inline" width={20} height={20} />
					</Tooltip>
				</h3>
			</li>

			<li className="flex gap-3">
				<Button
					size="lg"
					color="primary"
					variant="shadow"
					className="max-md:min-w-max"
					aria-label="Administration"
					isDisabled={isProduction}
					startContent={<LayoutDashboard />}
				>
					<span className="hidden md:inline">Administration</span>
				</Button>

				<Button
					size="lg"
					color="primary"
					variant="flat"
					className="max-md:min-w-max"
					aria-label="Statistiques"
					isDisabled={isProduction}
					startContent={<ChartLine />}
				>
					<span className="hidden md:inline">Statistiques</span>
				</Button>
			</li>

			<li className="flex gap-3">
				<Button
					size="lg"
					color="success"
					variant="flat"
					aria-label="Régénérer"
					isDisabled={isProduction}
					startContent={<RefreshCw />}
				>
					<span className="hidden md:inline">Régénérer</span>
				</Button>

				<Button
					size="lg"
					color="danger"
					variant="flat"
					aria-label="Supprimer"
					isDisabled={isProduction}
					startContent={<Trash2 />}
				>
					<span className="hidden md:inline">Supprimer</span>
				</Button>
			</li>
		</ul>
	);
}