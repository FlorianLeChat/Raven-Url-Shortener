//
// Interface des propriétés pour une erreur émise par le back-end PHP.
//
export interface ErrorProperties {
	// Code du message d'erreur.
	code: string;

	// Contenu du message d'erreur.
	message: string;

	// Liste des erreurs.
	errors?: {
		[key: string]: {
			// Code de l'erreur.
			code: string;

			// Message de l'erreur.
			message: string;
		}[];
	};
}