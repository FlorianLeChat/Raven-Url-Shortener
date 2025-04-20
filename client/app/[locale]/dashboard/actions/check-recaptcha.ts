//
// Action de vérification du jeton reCAPTCHA.
//  Source : https://github.com/FlorianLeChat/Simple-File-Storage/blob/25bc393c301fec229835658697bedd18c6ae7114/utilities/recaptcha.ts
//

"use server";

import { getTranslations } from "next-intl/server";
import type { RecaptchaValidation } from "@/interfaces/RecaptchaValidation";

export async function checkRecaptcha( token?: string )
{
	const messages = await getTranslations();

	if ( !token )
	{
		return {
			state: false,
			message: messages( "errors.recaptcha.missing_or_invalid" )
		};
	}

	const data = await fetch(
		`https://www.google.com/recaptcha/api/siteverify?secret=${ process.env.RECAPTCHA_SECRET_KEY }&response=${ token }`,
		{ method: "POST" }
	);

	if ( data.ok )
	{
		const json = ( await data.json() ) as RecaptchaValidation;
		const isInvalidResponse = !json.success || json.score < 0.7;

		if ( isInvalidResponse )
		{
			return {
				state: false,
				message: messages( "errors.recaptcha.score_invalid" )
			};
		}
	}
	else
	{
		return {
			state: false,
			message: messages( "errors.recaptcha.check_failed" )
		};
	}

	return {
		state: true
	};
}