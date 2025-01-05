//
// Composant des services de vérification via Google reCAPTCHA.
//

"use client";

import Script from "next/script";
import type { CookieValue } from "vanilla-cookieconsent";
import { useState, useEffect, useCallback } from "react";

export default function Recaptcha()
{
	// Déclaration des variables d'état.
	const [ recaptcha, setRecaptcha ] = useState( false );

	// Déclaration des constantes.
	const recaptchaUrl = new URL( "https://www.google.com/recaptcha/api.js" );
	recaptchaUrl.searchParams.append( "render", process.env.NEXT_PUBLIC_RECAPTCHA_PUBLIC_KEY ?? "" );

	// Activation des services Google reCAPTCHA au consentement des cookies.
	const onConsent = useCallback( ( event: CustomEventInit<{ cookie: CookieValue }> ) =>
	{
		const categories = event.detail?.cookie.categories;
		const isSecurity = categories?.some( ( category: string ) => category === "security" );

		setRecaptcha( isSecurity ?? false );
	}, [] );

	// Ajout et suppression de l'écouteur d'événements pour le consentement des cookies.
	useEffect( () =>
	{
		window.addEventListener( "cc:onConsent", onConsent );

		return () => window.removeEventListener( "cc:onConsent", onConsent );
	}, [ onConsent ] );

	// Affichage du rendu HTML du composant.
	return (
		process.env.NEXT_PUBLIC_RECAPTCHA_ENABLED === "true"
		&& recaptcha && <Script src={recaptchaUrl.href} strategy="lazyOnload" />
	);
}