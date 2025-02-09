//
// Action de vérification du jeton reCAPTCHA.
//  Source : https://github.com/FlorianLeChat/Simple-File-Storage/blob/25bc393c301fec229835658697bedd18c6ae7114/utilities/recaptcha.ts
//

"use server";

import { logger } from "@/utilities/pino";
import { getTranslations } from "next-intl/server";
import type { RecaptchaValidation } from "@/interfaces/RecaptchaValidation";

export async function checkRecaptcha( token?: string )
{
	// Vérification de la validité du jeton reCAPTCHA.
	const messages = await getTranslations();

	if ( !token )
	{
		return {
			state: false,
			message: messages( "errors.recaptcha.missing_or_invalid" )
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
		const json = ( await data.json() ) as RecaptchaValidation;
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
				message: messages( "errors.recaptcha.score_invalid" )
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
			message: messages( "errors.recaptcha.check_failed" )
		};
	}

	// Tout s'est bien passé.
	return {
		state: true
	};
}