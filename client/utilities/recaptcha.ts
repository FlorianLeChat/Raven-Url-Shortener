//
// Méthodes utilitaires pour les vérifications reCAPTCHA.
//

"use client";

export const getRecaptcha = async () =>
{
	// Vérification de l'activation des vérifications reCAPTCHA.
	if ( process.env.NEXT_PUBLIC_RECAPTCHA_ENABLED !== "true" )
	{
		return "";
	}

	// Vérification du fonctionnement du service reCAPTCHA dans
	//  le navigateur de l'utilisateur.
	if ( typeof window.grecaptcha === "undefined" )
	{
		return "";
	}

	// Création d'une promesse pour gérer le chargement des services
	//  de Google reCAPTCHA.
	return new Promise( ( resolve ) =>
	{
		// Attente de la disponibilité des services de Google reCAPTCHA.
		window.grecaptcha.ready( async () =>
		{
			// Récupération du jeton d'authentification reCAPTCHA
			//  auprès des serveurs de Google.
			const token = await window.grecaptcha.execute(
				process.env.NEXT_PUBLIC_RECAPTCHA_PUBLIC_KEY,
				{ action: "submit" }
			);

			return resolve( token );
		} );
	} );
};