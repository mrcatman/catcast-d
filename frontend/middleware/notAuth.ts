export default defineNuxtRouteMiddleware(to => {
	const {user} = useAuthStore();
	if (user) {
		return navigateTo(`/users/${user.id}`);
	}
})

