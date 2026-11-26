//
// Site footer component.
//

"use client";

import { Link } from "@heroui/react";
import { useTranslations } from "next-intl";

/**
 * Site footer with the copyright, the deployed version (set by the CI/CD pipeline)
 * and the CAPTCHA notice when the protection is enabled.
 */
export default function Footer()
{
	const version = process.env.NEXT_PUBLIC_VERSION;
	const messages = useTranslations( "footer" );

	return (
		<footer className="container mx-auto mt-auto max-w-[1440px] p-4 !pt-0 md:p-8">
			<p className="text-default-500 text-sm">
				© {new Date().getFullYear()} Raven Url Shortener. {messages( "rights_reserved" )}.
				{version && (
					<>
						{" "}
						<span className="tabular-nums">{messages( "version", { version } )}.</span>
					</>
				)}
			</p>

			{process.env.NEXT_PUBLIC_CAPTCHA_ENABLED === "true" && (
				<small className="text-default-500 text-sm">
					{messages.rich( "captcha_protected", {
						a: ( chunks ) => (
							<Link href="https://altcha.org/" className="text-primary text-sm" isExternal showAnchorIcon>
								{chunks}
							</Link>
						)
					} )}
				</small>
			)}
		</footer>
	);
}