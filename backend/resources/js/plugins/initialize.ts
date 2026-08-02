export default defineNuxtPlugin(async (nuxtApp) => {

	const { fetchUser } = useAuthStore();
	const { fetchConfig } = useConfigStore();

	await fetchConfig();
	await fetchUser();
})
