import { defineStore } from "pinia";

export const useThemeStore = defineStore('theme', () => {

	const theme = ref<boolean>('flat');
	const setTheme = (theme: string) => {
		theme.value = theme;
	}

	return {
		theme,
		setTheme
	}
});
