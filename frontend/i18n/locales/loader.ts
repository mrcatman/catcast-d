export default defineI18nLocale(async locale => {
	const { public: {apiUrl}} = useRuntimeConfig();

	const { data: messages } = await useFetch(`${apiUrl}locales/${locale}`)
	return messages.value;
})
