//
// Composant de gestion des boites de dialogue.
//

"use client";

import { Modal,
	Button,
	ModalBody,
	ModalHeader,
	ModalFooter,
	ModalContent } from "@heroui/react";
import { useMemo,
	useState,
	useContext,
	useCallback,
	createContext,
	type ReactNode } from "react";
import type { ModalOptionProps,
	ModalResultProps,
	ModalProviderProps } from "@/interfaces/ModalProperties";

const ModalContext = createContext<ModalProviderProps | null>( null );

export default function ModalProvider( {
	children
}: Readonly<{ children: ReactNode }> )
{
	// Déclaration des variables d'état.
	const [ modalState, setModalState ] = useState( {
		body: <></>,
		size: "md",
		title: <></>,
		isOpen: false,
		resolve: ( result: { confirmed: boolean; dismissed: boolean } ) =>
		{
			// Fonction de résolution par défaut, qui ne fait rien.
			// C'est utile seulement pour éviter les erreurs TypeScript.
			console.warn( "Modal resolved without a handler.", result );
		},
		cancelText: "Cancel",
		confirmText: "OK",
		isDismissable: true,
		hideCloseButton: false,
		isKeyboardDismissDisabled: true
	} );

	// Ouverture de la boite de dialogue avec une promesse.
	const showModal = useCallback(
		( options: ModalOptionProps ): Promise<ModalResultProps> =>
		{
			return new Promise( ( resolve ) =>
			{
				setModalState( {
					body: options.body,
					size: options.size ?? "md",
					title: options.title,
					isOpen: true,
					resolve,
					confirmText: options.confirmText ?? "OK",
					cancelText: options.cancelText ?? "Cancel",
					isDismissable: options.isDismissable ?? true,
					hideCloseButton: options.hideCloseButton ?? false,
					isKeyboardDismissDisabled: options.isDismissable ?? false
				} );
			} );
		},
		[]
	);

	// Fermeture de la boite de dialogue.
	const closeModal = () =>
	{
		setModalState( ( current ) => ( { ...current, isOpen: false } ) );
	};

	// Gestion de la confirmation de la boite de dialogue.
	const handleConfirm = () =>
	{
		modalState.resolve( { confirmed: true, dismissed: false } );
		closeModal();
	};

	// Gestion de l'annulation de la boite de dialogue.
	const handleCancel = () =>
	{
		modalState.resolve( { confirmed: false, dismissed: true } );
		closeModal();
	};

	// Affichage du rendu HTML du composant.
	const contextValue = useMemo( () => ( { showModal } ), [ showModal ] );

	return (
		<ModalContext.Provider value={contextValue}>
			{children}

			<Modal
				size={modalState.size as "md"}
				isOpen={modalState.isOpen}
				onClose={handleCancel}
				isDismissable={modalState.isDismissable}
				hideCloseButton={modalState.hideCloseButton}
				isKeyboardDismissDisabled={modalState.isKeyboardDismissDisabled}
			>
				<ModalContent>
					<ModalHeader>{modalState.title}</ModalHeader>

					<ModalBody>{modalState.body}</ModalBody>

					<ModalFooter>
						<Button variant="light" onPress={handleCancel}>
							{modalState.cancelText}
						</Button>

						<Button color="primary" onPress={handleConfirm}>
							{modalState.confirmText}
						</Button>
					</ModalFooter>
				</ModalContent>
			</Modal>
		</ModalContext.Provider>
	);
}

export const useModal = () =>
{
	// Récupération du contexte de la boite de dialogue.
	const context = useContext( ModalContext );

	if ( !context )
	{
		throw new Error( "useModal must be used within a ModalProvider" );
	}

	return context;
};