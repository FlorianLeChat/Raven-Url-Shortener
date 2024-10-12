//
// Abstraction pour les composants en provenance de NextUI.
//  Source : https://nextui.org/docs/frameworks/nextjs#setup-provider
//

"use client";

import type { ReactNode } from "react";
import { NextUIProvider as Provider } from "@nextui-org/react";

export function NextUIProvider( { children }: { children: ReactNode } )
{
	return <Provider>{children}</Provider>;
}