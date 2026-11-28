"use client";

import { solveChallengeWorkers } from "altcha-lib";

interface CaptchaChallenge
{
	algorithm: string;
	challenge: string;
	maxnumber: number;
	salt: string;
	signature: string;
}

/**
 * Gets an ALTCHA challenge from the API, solves its proof of work in Web Workers and returns
 *  the base64 payload the API expects in the `captcha` field of the link creation and report requests.
 * Returns `undefined` when the CAPTCHA is disabled, `null` when the challenge cannot be obtained or solved:
 *  the API then refuses the request, so the form stops before sending it.
 */
export const solveCaptchaChallenge = async () =>
{
	if ( process.env.NEXT_PUBLIC_CAPTCHA_ENABLED !== "true" )
	{
		return undefined;
	}

	try
	{
		const response = await fetch( `${ process.env.NEXT_PUBLIC_BACKEND_URL }/v1/captcha`, { cache: "no-store" } );

		if ( !response.ok )
		{
			console.log( "The CAPTCHA challenge could not be obtained from the API." );
			return null;
		}

		const challenge = ( await response.json() ) as CaptchaChallenge;
		const solution = await solveChallengeWorkers(
			() => new Worker( new URL( "altcha-lib/worker", import.meta.url ) ),
			navigator.hardwareConcurrency,
			challenge.challenge,
			challenge.salt,
			challenge.algorithm,
			challenge.maxnumber
		);

		if ( !solution )
		{
			console.log( "The CAPTCHA challenge could not be solved." );
			return null;
		}

		return btoa( JSON.stringify( {
			algorithm: challenge.algorithm,
			challenge: challenge.challenge,
			number: solution.number,
			salt: challenge.salt,
			signature: challenge.signature
		} ) );
	}
	catch ( error )
	{
		console.log( "An error occurred while solving the CAPTCHA challenge." );
		console.table( error );

		return null;
	}
};