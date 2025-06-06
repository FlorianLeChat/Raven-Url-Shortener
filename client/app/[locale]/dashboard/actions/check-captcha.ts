//
// Mécanisme de génération et de validation d'un défi CAPTCHA via Altcha.
//  Source : https://altcha.org/docs/v2/server-integration/#libraries
//

"use server";

import type { Payload } from "altcha-lib/types";
import { createChallenge, verifySolution } from "altcha-lib";

const solutions: Record<string, string> = {}; // https://altcha.org/docs/v2/security-recommendations/#replay-attacks

const CAPTCHA_HMAC = process.env.CAPTCHA_HMAC ?? "";
const CAPTCHA_MAX_NUMBER = 100_000;
const CAPTCHA_EXPIRATION = 60 * 1000; // 1 minute.

const generateRandomHex = ( length: number ) =>
{
	// Génère un tableau d'octets aléatoires de la longueur spécifiée
	//  et les convertit en une chaîne hexadécimale.
	const values = crypto.getRandomValues( new Uint8Array( length ) );
	const data = Array.from( values, ( byte ) =>
		byte.toString( 16 ).padStart( 2, "0" )
	);

	return data.join( "" );
};

export async function generateCaptchaChallenge()
{
	if ( process.env.NEXT_PUBLIC_CAPTCHA_ENABLED !== "true" )
	{
		return;
	}

	const salt = generateRandomHex( 32 );
	const duration = Date.now() + CAPTCHA_EXPIRATION;

	return createChallenge( {
		salt,
		expires: new Date( duration ),
		hmacKey: CAPTCHA_HMAC,
		maxNumber: CAPTCHA_MAX_NUMBER
	} );
}

export async function verifyCaptchaResolution( solution?: Payload )
{
	if ( process.env.NEXT_PUBLIC_CAPTCHA_ENABLED !== "true" )
	{
		// CAPTCHA désactivé, aucune vérification nécessaire.
		return true;
	}

	if ( !solution )
	{
		// Vérifie si la solution est définie.
		console.log( "No CAPTCHA solution provided." );

		return false;
	}

	if ( solutions[ solution.challenge ] )
	{
		// Vérifie si la solution a déjà été utilisée.
		console.log( "Solution already used for challenge:", solution.challenge );

		return false;
	}

	try
	{
		const verified = await verifySolution( solution, CAPTCHA_HMAC );

		if ( !verified )
		{
			// Vérifie si la solution est invalide.
			console.log( "Invalid solution for challenge:", solution.challenge );

			return false;
		}

		// Enregistre la solution pour éviter les attaques par relecture.
		solutions[ solution.challenge ] = `${ solution.number }`;

		console.log( "CAPTCHA solution verified successfully for challenge:", solution.challenge );

		return true;
	}
	catch ( error )
	{
		console.log( "An error occurred while verifying the CAPTCHA solution.", error );

		return false;
	}
}