import { lazy } from "react";
import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";

import { features } from "@/config/features";
import { fetchMetadata } from "@/utilities/metadata";

const FeatureCard = lazy( () => import( "./components/feature-card" ) );
const LegalConsent = lazy( () => import( "./components/consent-legal" ) );
const GatewayButton = lazy( () => import( "./components/gateway-button" ) );

export async function generateMetadata(): Promise<Metadata>
{
	const metadata = await fetchMetadata();
	const messages = await getTranslations();

	return {
		title: `${ messages( "header.home" ) } – ${ metadata.title }`
	};
}

export default async function Page( {
	params
}: Readonly<{
	params: Promise<{ locale: string }>;
}> )
{
	const { locale } = await params;

	setRequestLocale( locale );

	const metadata = await fetchMetadata();
	const messages = await getTranslations();

	return (
		<>
			<header className="container mx-auto max-w-360 p-4 md:p-8">
				<h1 className="inline text-4xl font-semibold tracking-tight lg:text-5xl">
					{messages( "index.title" )}
				</h1>

				<h2 className="mt-2 bg-linear-to-b from-[#5EA2EF] to-[#0072F5] bg-clip-text text-4xl font-semibold tracking-tight text-transparent lg:text-5xl">
					{metadata.title as string}
				</h2>

				<p className="text-default-500 my-2 w-full max-w-full text-lg font-normal md:w-1/2 lg:text-xl">
					{messages( "index.description" )}
				</p>

				<a
					rel="noopener noreferrer"
					href={metadata.source}
					title="GitHub"
					target="_blank"
					className="group fixed top-0 right-0 bottom-auto left-auto [clip-path:polygon(0_0,100%_0,100%_100%)] max-sm:hidden"
					aria-label="GitHub"
					data-umami-event="Open the creator's GitHub profile"
					data-umami-event-url={metadata.source}
				>
					<svg width="80" height="80" viewBox="0 0 250 250">
						<path
							d="M0 0l115 115h15l12 27 108 108V0z"
							className="fill-primary"
						/>
						<path
							d="M128 109c-15-9-9-19-9-19 3-7 2-11 2-11-1-7 3-2 3-2 4 5 2 11 2 11-3 10 5 15 9 16"
							className="max-md:motion-safe:animate-github md:motion-safe:group-hover:animate-github origin-[130px_106px] fill-current"
						/>
						<path
							d="M115 115s4 2 5 0l14-14c3-2 6-3 8-3-8-11-15-24 2-41 5-5 10-7 16-7 1-2 3-7 12-11 0 0
							5 3 7 16 4 2 8 5 12 9s7 8 9 12c14 3 17 7 17 7-4 8-9 11-11 11 0 6-2 11-7 16-16 16-30 10-41
							2 0 3-1 7-5 11l-12 11c-1 1 1 5 1 5z"
							className="fill-current"
						/>
					</svg>
				</a>
			</header>

			<main className="container mx-auto max-w-360 p-4 pt-0! md:p-8">
				<section className="mb-8">
					<header className="mb-8">
						{messages.rich( "index.features.title", {
							h1: ( children ) => (
								<h1 className="inline text-2xl font-semibold tracking-tight lg:text-3xl">
									{children}
								</h1>
							),
							pink: ( children ) => (
								<h1 className="inline bg-linear-to-b from-[#FF72E1] to-[#F54C7A] bg-clip-text text-2xl font-semibold tracking-tight text-transparent lg:text-3xl">
									{children}
								</h1>
							)
						} )}
					</header>

					<article className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
						{features.map( ( { id, title, description, icon } ) => (
							<FeatureCard
								key={id}
								icon={icon}
								title={messages( title )}
								description={messages( description )}
							/>
						) )}
					</article>
				</section>

				<section className="mb-8">
					<header className="mb-6">
						{messages.rich( "index.ready.title", {
							h1: ( children ) => (
								<h1 className="inline text-2xl font-semibold tracking-tight lg:text-3xl">
									{children}
								</h1>
							),
							green: ( children ) => (
								<h1 className="inline bg-linear-to-b from-[#6FEE8D] to-[#17c964] bg-clip-text text-2xl font-semibold tracking-tight text-transparent lg:text-3xl">
									{children}
								</h1>
							)
						} )}
					</header>

					<GatewayButton />
				</section>
			</main>

			<LegalConsent />
		</>
	);
}