//
// Action de vérification du jeton reCAPTCHA.
//  Source : https://github.com/FlorianLeChat/Simple-File-Storage/blob/25bc393c301fec229835658697bedd18c6ae7114/utilities/recaptcha.ts
//

"use server";

import { logger } from "@/utilities/pino";
import type { RecaptchaResponse } from "@/interfaces/Recaptcha";

export async function checkRecaptcha( token?: string )
{
	// Vérification de la validité du jeton reCAPTCHA.
	if ( !token )
	{
		return {
			state: false,
			message: "Le jeton reCAPTCHA est manquant ou invalide."
		};
	}

	// Vérification de la validité du jeton reCAPTCHA.
	const data = await fetch(
		`https://www.google.com/recaptcha/api/siteverify?secret=${ process.env.RECAPTCHA_SECRET_KEY }&response=${ token }`,
		{ method: "POST" }
	);

	if ( data.ok )
	{
		// Vérification du score de confiance attribué à l'utilisateur.
		const json = ( await data.json() ) as RecaptchaResponse;
		const isInvalidResponse = !json.success || json.score < 0.7;

		logger.info(
			{ source: __dirname, json },
			"reCAPTCHA verification response."
		);

		if ( isInvalidResponse )
		{
			// En cas de score insuffisant ou si la réponse est invalide,
			//  on bloque la requête courante.
			return {
				state: false,
				message: "La vérification reCAPTCHA a échouée."
			};
		}
	}
	else
	{
		// En cas d'erreur lors de la vérification du jeton reCAPTCHA.
		logger.error(
			{ source: __dirname, status: data.status },
			"An error occurred while checking the reCAPTCHA token."
		);

		return {
			state: false,
			message: "Une erreur s'est produite lors de la vérification du jeton reCAPTCHA."
		};
	}

	// Tout s'est bien passé.
	return {
		state: true
	};
}