//
// Interface des propriétés pour les informations du serveur.
//
import type { ReactNode } from "react";

export interface ModalOptionProps
{
	// Titre de la boite de dialogue.
	title: string;

	// Contenu de la boite de dialogue.
	body: ReactNode | string;

	// Icône à afficher dans la boite de dialogue.
	icon: ReactNode;

	// Taille de la boite de dialogue.
	size?: string;

	// Fonction appelée lors de l'ouverture de la boite de dialogue.
	onOpen?: () => void;

	// Fonction appelée lors de la fermeture de la boite de dialogue.
	onClose?: () => void;

	// Texte du bouton de confirmation.
	confirmText?: string;

	// Type du bouton de confirmation.
	confirmColor?: string;

	// Texte du bouton d'annulation.
	cancelText?: string;

	// Type du bouton d'annulation.
	cancelColor?: string;

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

	// Fonction de fermeture de la boite de dialogue.
	closeModal: () => void;
}