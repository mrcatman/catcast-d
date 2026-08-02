export default defineNuxtRouteMiddleware(to => {
	const { registrationEnabled } = useConfigStore();

	if (!registrationEnabled) {
		return navigateTo('/auth/login')
	}
})
