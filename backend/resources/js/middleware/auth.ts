export default defineNuxtRouteMiddleware(to => {
	const {user} = useAuthStore();
	if (!user) {
		return navigateTo('/auth/login');
	}
})

