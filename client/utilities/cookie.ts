//
// Fonctions utilitaires pour manipuler les cookies.
//

"use client";

interface CookieOptions
{
	// Nom du cookie.
	name: string;

	// Valeur du cookie.
	value: string;

	// Durée de vie du cookie en jours.
	days?: number;

	// Chemin du cookie.
	path?: string;

	// Indique si le cookie doit être disponible uniquement en HTTPS.
	secure?: boolean;
}

export const setCookie = ( { ...options }: CookieOptions ) =>
{
	// Valeurs par défaut pour les options.
	options.days = options.days ?? 365;
	options.path = options.path ?? "/";
	options.secure = options.secure ?? true;

	// Définition d'un cookie.
	const expires = new Date();
	expires.setTime( expires.getTime() + options.days * 24 * 60 * 60 * 1000 );

	document.cookie = `${ options.name }=${ options.value }; expires=${ expires.toUTCString() }; path=${ options.path }; SameSite=Lax${ options.secure ? "; Secure" : "" }`;
};

export const hasCookie = ( name: string ): boolean =>
{
	// Vérifie si un cookie existe.
	return getCookie( name ) !== null;
};

export const getCookie = ( name: string ): string | null =>
{
	// Récupère la valeur d'un cookie par son nom.
	const cookies = document.cookie.split( ";" );

	for ( const cookie of cookies )
	{
		const [ key, value ] = cookie.trim().split( "=" );

		if ( key === name )
		{
			return decodeURIComponent( value );
		}
	}

	return null;
};

export const deleteCookie = ( name: string, path = "/" ) =>
{
	// Supprime un cookie.
	document.cookie = `${ name }=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=${ path }; SameSite=Lax`;
};