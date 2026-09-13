import { useModalsStore, type ModalOptions } from "@/stores/modals";

export type { ModalOptions };

export const useModal = () => {
	const { showModal, hideModal, hideAllModals } = useModalsStore();

	return {
		showModal,
		hideModal,
		hideAllModals,
	}
}
