//
// Abstraction pour les composants en provenance de HeroUI.
//  Source : https://www.heroui.com/docs/frameworks/nextjs#setup-provider
//

"use client";

import type { ReactNode } from "react";
import { HeroUIProvider as Provider } from "@heroui/react";

export function HeroUIProvider( {
	children,
	className
}: Readonly<{
	children: ReactNode;
	className: string;
}> )
{
	return <Provider className={className}>{children}</Provider>;
}