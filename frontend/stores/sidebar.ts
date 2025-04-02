import { defineStore } from "pinia";

export const useSidebarStore = defineStore('sidebar', () => {

	const sidebarOpened = ref<boolean>(false);
	const toggleSidebar = () => {
		sidebarOpened.value = !sidebarOpened.value;
	}

	return {
		sidebarOpened,
		toggleSidebar
	}
});
