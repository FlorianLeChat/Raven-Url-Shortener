//
// Interface des propriétés pour une erreur émise par le back-end PHP.
//
export interface ErrorProperties {
	// Code du message d'erreur.
	code: string;

	// Contenu du message d'erreur.
	message: string;
}