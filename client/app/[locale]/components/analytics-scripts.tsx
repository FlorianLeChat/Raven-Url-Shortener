"use client";

import Script from "next/script";
import { useEffect, useState } from "react";

/**
 * Tells whether the analytics scripts may load for this visitor.
 *
 * The recorder has no privacy option and the tracker ignores Global Privacy
 * Control, so honouring Do Not Track or GPC means never loading them. Runs
 * in an effect since the server render cannot read the browser's preference.
 * The cast covers GPC, missing from TypeScript's DOM library.
 *
 * @returns `true` when the visitor sends no opt-out signal that applies.
 * @author Claude
 */
const isTrackingAllowed = (): boolean =>
{
	const privacyNavigator = navigator as Navigator & { globalPrivacyControl?: boolean };
	const respectOptOut = process.env.NEXT_PUBLIC_ANALYTICS_RESPECT_DNT === "true";
	const dntRequested = [ "1", "yes" ].includes( privacyNavigator.doNotTrack ?? "" );
	const gpcRequested = privacyNavigator.globalPrivacyControl === true;
	const optedOut = dntRequested || gpcRequested;

	return !( respectOptOut && optedOut );
};

/**
 * Loads the Umami tracker and recorder once the visitor's opt-out signals
 * have been read, and renders nothing when one of them applies.
 *
 * @returns The two analytics scripts, or `null`.
 * @author Claude
 */
export default function AnalyticsScripts()
{
	const [ canTrack, setCanTrack ] = useState( false );

	useEffect( () =>
	{
		setCanTrack( isTrackingAllowed() );
	}, [] );

	if ( !canTrack )
	{
		return null;
	}

	return (
		<>
			<Script
				src={process.env.NEXT_PUBLIC_ANALYTICS_ENDPOINT + "/script.js"}
				strategy="lazyOnload"
				data-errors="true"
				data-website-id={process.env.NEXT_PUBLIC_ANALYTICS_PROJECT_ID}
				data-performance="true"
				data-environment={process.env.NEXT_PUBLIC_ENV}
				data-do-not-track={process.env.NEXT_PUBLIC_ANALYTICS_RESPECT_DNT}
				data-exclude-hash="true"
				data-exclude-search="true"
			/>

			<Script
				src={process.env.NEXT_PUBLIC_ANALYTICS_ENDPOINT + "/recorder.js"}
				strategy="lazyOnload"
				data-website-id={process.env.NEXT_PUBLIC_ANALYTICS_PROJECT_ID}
			/>
		</>
	);
}