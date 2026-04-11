"use client";

import { useEffect } from "react";
import { useMessages } from "next-intl";
import { usePathname } from "next/navigation";
import { run,
	type ConsentModalOptions,
	type PreferencesModalOptions } from "vanilla-cookieconsent";

export default function CookieConsent()
{
	const pathname = usePathname();
	const messages = useMessages() as unknown as {
		form: Record<string, Record<string, string>>;
		consentModal: ConsentModalOptions;
		preferencesModal: PreferencesModalOptions;
	};

	useEffect( () =>
	{
		run( {
			disablePageInteraction: true,
			hideFromBots: process.env.NEXT_PUBLIC_ENV === "production",
			autoShow:
				process.env.NEXT_PUBLIC_ENV === "production"
				&& !pathname.startsWith( "/legal" ),
			cookie: {
				name: "NEXT_COOKIE",
				path: "/"
			},
			guiOptions: {
				consentModal: {
					layout: "bar",
					position: "bottom center"
				}
			},
			categories: {
				necessary: {
					enabled: true,
					readOnly: true
				},
				analytics: {
					autoClear: {
						cookies: [
							{
								name: /^(_ga|_gid)/
							}
						]
					}
				}
			},
			language: {
				default: "en",
				translations: {
					en: {
						consentModal: messages.consentModal,
						preferencesModal: messages.preferencesModal
					}
				}
			},
			onChange: () =>
			{
				globalThis.location.reload();
			}
		} );
	}, [ pathname, messages ] );

	return null;
}