import "../layout.css";
import "vanilla-cookieconsent/dist/cookieconsent.css";

import Script from "next/script";
import { Inter } from "next/font/google";
import { lazy, type ReactNode } from "react";
import { getMessages, setRequestLocale } from "next-intl/server";

import { getDomain,
	getTimeZoneName,
	getTimeZoneOffset } from "@/utilities/server";
import { getLanguages } from "@/utilities/i18n";
import { fetchMetadata } from "@/utilities/metadata";

import type { Viewport } from "next";

const Footer = lazy( () => import( "@/components/global-footer" ) );
const Providers = lazy( () => import( "@/components/global-providers" ) );
const CookieConsent = lazy( () => import( "@/components/consent-cookie" ) );

const languages = getLanguages();
const inter = Inter( {
	subsets: [ "latin" ],
	display: "swap"
} );

export const viewport: Viewport = {
	viewportFit: "cover",
	themeColor: [
		{ media: "(prefers-color-scheme: light)", color: "#c2d0e0" },
		{ media: "(prefers-color-scheme: dark)", color: "#0072f5" }
	]
};

export async function generateMetadata()
{
	return fetchMetadata();
}

export function generateStaticParams()
{
	return languages.map( ( locale ) => ( { locale } ) );
}

export default async function Layout( {
	children,
	params
}: Readonly<{
	children: ReactNode;
	params: Promise<{ locale: string }>;
}> )
{
	const { locale } = await params;
	const messages = await getMessages();

	setRequestLocale( locale );

	if ( !languages.includes( locale ) )
	{
		return null;
	}

	const serverData = {
		locale,
		domain: await getDomain(),
		offset: getTimeZoneOffset(),
		timezone: getTimeZoneName()
	};

	return (
		<html
			lang={locale}
			className={`text-foreground light:bg-[whitesmoke] antialiased ${ inter.className }`}
			suppressHydrationWarning
		>
			<head>
				<script
					dangerouslySetInnerHTML={{
						__html: `
							const element = document.documentElement;
							const target = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";

							element.classList.remove("light", "dark");
							element.classList.add(target);
							element.style.colorScheme = target;

							if (target === "dark") {
								element.classList.add("cc--darkmode");
							}
						`
					}}
				/>
			</head>

			{process.env.NEXT_PUBLIC_ANALYTICS_ENABLED === "true" && (
				<Script
					src="/script.js"
					strategy="lazyOnload"
					data-website-id={
						process.env.NEXT_PUBLIC_ANALYTICS_PROJECT_ID
					}
					data-do-not-track="true"
					data-exclude-hash="true"
					data-exclude-search={
						process.env.NEXT_PUBLIC_ANALYTICS_RESPECT_DNT
					}
				/>
			)}

			<body>
				<video
					loop
					muted
					autoPlay
					className="fixed -z-10 hidden size-full object-cover opacity-25 dark:block"
				>
					<source
						src={`${ process.env.__NEXT_ROUTER_BASEPATH }/assets/videos/background.mp4`}
						type="video/mp4"
					/>
				</video>

				<Providers
					locale={locale}
					messages={messages}
					serverData={serverData}
				>
					{children}

					<Footer />
					<CookieConsent />
				</Providers>
			</body>
		</html>
	);
}