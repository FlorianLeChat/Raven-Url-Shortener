//
// Interface des propriétés pour les informations d'un lien raccourci.
//
export interface LinkProperties {
	// Identifiant unique.
	id: string;

	// URL d'origine du lien.
	url: string;

	// Slug personnalisé du lien.
	slug: string;

	// État d'activation du lien.
	enabled: boolean;

	// Code QR du lien.
	qrCode: string | null | undefined;

	// Date d'expiration du lien.
	expiration: string | null | undefined;

	// Date de la dernière visite du lien.
	visitedAt: {
		date: string;
		timezone_type: number;
		timezone: string;
	};

	// Date de création du lien.
	createdAt: {
		date: string;
		timezone_type: number;
		timezone: string;
	};

	// Date de dernière mise à jour du lien.
	updatedAt?: {
		date: string;
		timezone_type: number;
		timezone: string;
	};
}