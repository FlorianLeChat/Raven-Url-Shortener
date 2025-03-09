//
// Abstraction pour les composants en provenance de HeroUI.
//  Source : https://www.heroui.com/docs/frameworks/nextjs#setup-provider
//

"use client";

import type { ReactNode } from "react";
import { HeroUIProvider as LayoutProvider, ToastProvider } from "@heroui/react";

export function HeroUIProvider( {
	children,
	className
}: Readonly<{
	children: ReactNode;
	className: string;
}> )
{
	return (
		<LayoutProvider className={className}>
			<ToastProvider
				placement="top-right"
				toastProps={{
					timeout: 10000
				}}
			/>

			{children}
		</LayoutProvider>
	);
}