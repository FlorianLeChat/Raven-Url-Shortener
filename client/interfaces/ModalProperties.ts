//
// Interface des propriétés pour les informations du serveur.
//
import type { ReactElement } from "react";

export interface ModalOptionProps
{
	// Titre de la boite de dialogue.
	title: ReactElement;

	// Contenu de la boite de dialogue.
	body: ReactElement;

	// Taille de la boite de dialogue.
	size?: string;

	// Texte du bouton de confirmation.
	confirmText?: string;

	// Texte du bouton d'annulation.
	cancelText?: string;

	// Indique si la boite de dialogue peut être fermée
	//  en cliquant en dehors de celle-ci ou via le clavier.
	isDismissable?: boolean;

	// Indique si le bouton de fermeture doit être masqué.
	hideCloseButton?: boolean;
}

export interface ModalResultProps
{
	// Indique si l'utilisateur a confirmé l'action.
	confirmed: boolean;

	// Indique si l'utilisateur a annulé l'action.
	dismissed: boolean;
}

export interface ModalProviderProps
{
	// Fonction d'ouverture de la boite de dialogue.
	showModal: ( options: ModalOptionProps ) => Promise<ModalResultProps>;
}