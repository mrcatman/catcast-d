import { defineStore } from "pinia";
import { useApi } from "../composables/useApi";

export const useAuthStore = defineStore('auth', () => {
	const { request } = useApi();

	const user = ref<Users.Item>();

	const setUser = (u: Users.Item) => {
		user.value = u;
	}

	const loggedIn = computed(() => {
		return !!user.value
	});

	const fetchUser = async () => {
		const { user } = await request.get('/auth/me');
		setUser(user);
	}

	const logout = () => {

	}

	return {
		loggedIn,
		user,
		setUser,
		fetchUser,
		logout,
	}
});
