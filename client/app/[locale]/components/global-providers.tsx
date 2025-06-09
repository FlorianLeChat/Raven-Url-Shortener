//
// Composant de groupage des providers de l'application.
//

"use client";

import pick from "lodash/pick";
import { lazy } from "react";
import { HeroUIProvider } from "@/utilities/hero-ui";
import { NextIntlClientProvider } from "next-intl";
import type { ServerContextProps } from "@/interfaces/ServerProperties";

const ModalProvider = lazy( () => import( "./provider-modal" ) );
const ServerProvider = lazy( () => import( "./provider-server" ) );

export default function Providers( {
	children,
	locale,
	messages,
	serverData
}: Readonly<{
	children: React.ReactNode;
	locale: string;
	messages: Record<string, unknown>;
	serverData: ServerContextProps;
}> )
{
	return (
		<NextIntlClientProvider
			locale={locale}
			messages={pick(
				messages,
				"errors",
				"footer",
				"summary",
				"redirect",
				"dashboard",
				"navigation",
				"index.ready",
				"consentModal",
				"index.consent",
				"preferencesModal"
			)}
			timeZone={process.env.TZ}
		>
			<HeroUIProvider className="flex min-h-screen flex-col">
				<ServerProvider value={serverData}>
					<ModalProvider>{children}</ModalProvider>
				</ServerProvider>
			</HeroUIProvider>
		</NextIntlClientProvider>
	);
}