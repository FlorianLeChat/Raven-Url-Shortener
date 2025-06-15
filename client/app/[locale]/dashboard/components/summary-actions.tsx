//
// Composant des actions disponibles dans le récapitulatif.
//

"use client";

import { useTranslations } from "next-intl";
import { Button, Tooltip } from "@heroui/react";
import { Info, Trash2, Settings, ChartLine, RefreshCw } from "lucide-react";

export default function SummaryActions()
{
	// Déclaration des variables d'état.
	const messages = useTranslations( "summary" );

	// Déclaration des constantes.
	const isProduction = process.env.NEXT_PUBLIC_ENV === "production";

	// Affichage du rendu HTML du composant.
	return (
		<ul className="flex-col lg:flex">
			<li>
				<h3 className="mb-3 inline-flex items-center text-xl font-semibold">
					{messages( "actions.title" )}
					<Tooltip content={messages( "actions.wip" )}>
						<Info className="ml-2 inline" width={20} height={20} />
					</Tooltip>
				</h3>
			</li>

			<li className="gap-3 max-lg:mr-3 max-lg:inline-flex lg:mb-3 lg:flex">
				<Button
					size="lg"
					color="primary"
					variant="shadow"
					className="max-md:min-w-max"
					aria-label={messages( "actions.administration" )}
					isDisabled={isProduction}
					startContent={<Settings />}
				>
					<span className="hidden md:inline">
						{messages( "actions.administration" )}
					</span>
				</Button>

				<Button
					size="lg"
					color="primary"
					variant="flat"
					className="max-md:min-w-max"
					aria-label={messages( "actions.statistics" )}
					isDisabled={isProduction}
					startContent={<ChartLine />}
				>
					<span className="hidden md:inline">
						{messages( "actions.statistics" )}
					</span>
				</Button>
			</li>

			<li className="gap-3 max-lg:inline-flex lg:flex">
				<Button
					size="lg"
					color="success"
					variant="flat"
					className="max-md:min-w-max"
					aria-label={messages( "actions.regenerate" )}
					isDisabled={isProduction}
					startContent={<RefreshCw />}
				>
					<span className="hidden md:inline">
						{messages( "actions.regenerate" )}
					</span>
				</Button>

				<Button
					size="lg"
					color="danger"
					variant="flat"
					className="max-md:min-w-max"
					aria-label={messages( "actions.remove" )}
					isDisabled={isProduction}
					startContent={<Trash2 />}
				>
					<span className="hidden md:inline">
						{messages( "actions.remove" )}
					</span>
				</Button>
			</li>
		</ul>
	);
}