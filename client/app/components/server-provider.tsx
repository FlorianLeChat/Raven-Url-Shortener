//
// Composant de partage des informations du serveur.
//

"use client";

import { createContext, type ReactNode } from "react";
import type { ServerContextProps,
	ServerProviderProps } from "../interfaces/ServerProperties";

// Exportation du context du composant.
export const ServerContext = createContext<ServerContextProps | null>( null );

// Exportation du composant.
export default function ServerProvider( {
	children,
	value
}: Readonly<{
	children: ReactNode;
	value: ServerProviderProps["value"];
}> )
{
	return (
		<ServerContext.Provider value={value}>
			{children}
		</ServerContext.Provider>
	);
}