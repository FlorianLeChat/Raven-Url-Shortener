//
// Interface des propriétés pour les informations du serveur.
//
import type { ReactNode } from "react";

export interface ServerContextProps
{
	locale: string;
	domain: string;
	offset: string;
	timezone: string;
}

export interface ServerProviderProps
{
	children: ReactNode;
	value: ServerContextProps;
}