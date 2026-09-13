import { defineStore } from "pinia";
import { markRaw } from "vue";
import type { Component } from "vue";

export interface ModalOptions<T = any> {
	title?: string;
	text?: string;

	confirm?: boolean;
	buttonText?: string;
	buttonColor?: string;
	cancelText?: string;

	component?: Component;
	props?: Record<string, any>;

	formValues?: Partial<T>;

	buttonDisabledFn?: (values: Partial<T>, instance?: any) => boolean;
	fn?: (values: T, instance?: any) => Promise<unknown> | unknown;
}

export type OpenedModal<T = any> = ModalOptions<T> & {
	id: number;
}

export const useModalsStore = defineStore('modals', () => {

	const openedModals = ref<OpenedModal[]>([]);

	let lastId = 0;

	const showModal = <T>(modal: ModalOptions<T>): number => {
		lastId++;
		openedModals.value.push({
			...modal,
			// openedModals is a deep ref, so a component definition stored in it
			// would be turned into a reactive proxy.
			component: modal.component ? markRaw(modal.component) : undefined,
			id: lastId
		});

		return lastId;
	}

	const hideModal = (id: number) => {
		openedModals.value = openedModals.value.filter(modal => modal.id !== id);
	}

	const hideAllModals = () => {
		openedModals.value = [];
	}

	return {
		openedModals,
		showModal,
		hideModal,
		hideAllModals
	}
});
