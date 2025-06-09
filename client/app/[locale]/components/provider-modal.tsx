//
// Composant de gestion des boites de dialogue.
//

"use client";

import { Form,
	Modal,
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
	type ReactNode,
	type FormEvent } from "react";
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
		body: "" as ReactNode,
		icon: "" as ReactNode,
		size: "md",
		title: "",
		isOpen: false,
		onOpen: ( event: FormEvent<HTMLFormElement> ) =>
		{
			// Fonction de substitution pour l'ouverture de la boite de dialogue.
			console.warn( "Modal opened without a handler.", event );
		},
		onClose: () =>
		{
			// Fonction de substitution pour la fermeture de la boite de dialogue.
			console.warn( "Modal closed without a handler." );
		},
		resolve: ( result: { confirmed: boolean; dismissed: boolean } ) =>
		{
			// Fonction de substitution pour la résolution de la boite de dialogue.
			console.warn( "Modal resolved without a handler.", result );
		},
		cancelText: "Cancel",
		cancelColor: "default",
		confirmText: "OK",
		confirmColor: "primary",
		isDismissable: true,
		hideCloseButton: false,
		isKeyboardDismissDisabled: true
	} );

	// Ouverture de la boite de dialogue avec les options fournies.
	const showModal = useCallback(
		( options: ModalOptionProps ): Promise<ModalResultProps> =>
		{
			return new Promise( ( resolve ) =>
			{
				setModalState( {
					body: options.body,
					icon: options.icon,
					size: options.size ?? "md",
					title: options.title,
					resolve,
					isOpen: true,
					onOpen: options.onOpen ?? handleConfirm,
					onClose: options.onClose ?? handleCancel,
					cancelText: options.cancelText ?? modalState.cancelText,
					cancelColor: options.cancelColor ?? modalState.cancelColor,
					confirmText: options.confirmText ?? modalState.confirmText,
					confirmColor:
						options.confirmColor ?? modalState.confirmColor,
					isDismissable:
						options.isDismissable ?? modalState.isDismissable,
					hideCloseButton:
						options.hideCloseButton ?? modalState.hideCloseButton,
					isKeyboardDismissDisabled:
						options.isDismissable ?? modalState.isDismissable
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
	const handleConfirm = ( event: FormEvent<HTMLFormElement> ) =>
	{
		event.preventDefault();

		if ( modalState.onOpen )
		{
			modalState.onOpen( event );
		}
		else
		{
			modalState.resolve( { confirmed: true, dismissed: false } );
		}

		closeModal();
	};

	// Gestion de l'annulation de la boite de dialogue.
	const handleCancel = () =>
	{
		if ( modalState.onClose )
		{
			modalState.onClose();
		}
		else
		{
			modalState.resolve( { confirmed: false, dismissed: true } );
		}

		closeModal();
	};

	// Affichage du rendu HTML du composant.
	const contextValue = useMemo(
		() => ( { showModal, closeModal } ),
		[ showModal, closeModal ]
	);

	return (
		<ModalContext.Provider value={contextValue}>
			{children}

			<Modal
				size={modalState.size as "md"}
				isOpen={modalState.isOpen}
				onClose={handleCancel}
				backdrop="blur"
				isDismissable={modalState.isDismissable}
				hideCloseButton={modalState.hideCloseButton}
				isKeyboardDismissDisabled={modalState.isKeyboardDismissDisabled}
			>
				<ModalContent>
					<ModalHeader className="flex items-center gap-2">
						{modalState.icon}
						{modalState.title}
					</ModalHeader>

					<Form onSubmit={handleConfirm} validationBehavior="native">
						<ModalBody>{modalState.body}</ModalBody>

						<ModalFooter>
							<Button
								type="button"
								color={modalState.cancelColor as "default"}
								variant="flat"
								onPress={handleCancel}
							>
								{modalState.cancelText}
							</Button>

							<Button
								type="submit"
								color={modalState.confirmColor as "primary"}
							>
								{modalState.confirmText}
							</Button>
						</ModalFooter>
					</Form>
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