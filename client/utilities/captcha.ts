//
// Récupération et résolution des défis CAPTCHA pour les actions de formulaire.
//  Source : https://nextjs.org/docs/app/building-your-application/data-fetching/server-actions-and-mutations#non-form-elements
//

"use client";

import { solveChallengeWorkers } from "altcha-lib";
import { generateCaptchaChallenge } from "../app/[locale]/dashboard/actions/check-captcha";

export const solveCaptchaChallenge = async () =>
{
	// Vérification de l'activation du CAPTCHA.
	if ( process.env.NEXT_PUBLIC_CAPTCHA_ENABLED !== "true" )
	{
		return;
	}

	// Résolution du défi et obtention d'une solution.
	const challenge = await generateCaptchaChallenge();

	if ( !challenge )
	{
		console.log( "CAPTCHA is disabled or challenge generation failed." );
		return;
	}

	console.log( "Generated CAPTCHA challenge" );
	console.table( challenge );

	const solution = await solveChallengeWorkers(
		() => new Worker( new URL( "altcha-lib/worker", import.meta.url ) ),
		navigator.hardwareConcurrency,
		challenge.challenge,
		challenge.salt,
		challenge.algorithm,
		challenge.maxnumber
	);

	console.log( "Solving CAPTCHA challenge" );
	console.table( solution );

	// Transmission de la solution CAPTCHA.
	return {
		salt: challenge.salt,
		number: solution?.number ?? 0,
		algorithm: challenge.algorithm,
		challenge: challenge.challenge,
		signature: challenge.signature
	};
};