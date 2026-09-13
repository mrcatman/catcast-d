import type { UseFetchOptions } from 'nuxt/app'

interface Replacements {
	[key: string]: string | number;
}

const createUrl = (url: string, replacements?: Replacements): string => {
	if (!replacements) {
		return url;
	}
	for (const key in replacements) {
		url = url.replace(`:${key}`, String(replacements[key]));
	}

	return url;
}

export const useApi = () => {

	const {public: {apiUrl}} = useRuntimeConfig()
	const { newAlert } = useAlertsStore();

	const apiRequest = $fetch.create({
		baseURL: apiUrl,
		credentials: 'include',
		onResponse({response}) {
			if (response.status === 200 && response._data?.message) {
				newAlert({
					type: 'success',
					text: response._data.message,
				})
			}
		},
		onResponseError({response}) {
			if (response._data?.message) {
				newAlert({
					type: 'error',
					text: response._data.message,
				})
			}
		}
	})

	const request = {
		get: <U extends Api.ApiUrl<'Get'>>(url: U, options?: Api.RequestOptions<'Get', U>, replacements?: Replacements): Promise<Api.ResponseType<'Get', U>> => {
			return apiRequest(createUrl(url as string, replacements), {
				method: 'GET',
				...options
			})
		},
		post: <U extends Api.ApiUrl<'Post'>>(url: U, options: Api.RequestOptions<'Post', U>, replacements?: Replacements): Promise<Api.ResponseType<'Post', U>> => {
			return apiRequest(createUrl(url as string, replacements), {
				method: 'POST',
				...options
			})
		},
		put: <U extends Api.ApiUrl<'Put'>>(url: U, options: Api.RequestOptions<'Put', U>, replacements?: Replacements): Promise<Api.ResponseType<'Put', U>> => {
			return apiRequest(createUrl(url as string, replacements), {
				method: 'PUT',
				...options
			})
		},
		delete: <U extends Api.ApiUrl<'Delete'>>(url: U, options?: Api.RequestOptions<'Delete', U>, replacements?: Replacements): Promise<Api.ResponseType<'Delete', U>> => {
			return apiRequest(createUrl(url as string, replacements), {
				method: 'DELETE',
				...options
			})
		}
	}

	const useRequest = <U extends Api.ApiUrl<M>, M extends Api.ApiMethod = 'Get'>(
		url: U,
		options?: UseFetchOptions<Api.ResponseType<M, U>> & {
			method?: M
		} & Partial<Api.RequestOptions<M, U>>,
		replacements?: Replacements
	) => {
		const result = useFetch<Api.ResponseType<M, U>>(createUrl(url as string, replacements), {
			...options,
			method: (options?.method || 'Get').toUpperCase(),
			$fetch: apiRequest
		} as UseFetchOptions<Api.ResponseType<M, U>>)

		return Object.assign(result, {
			loading: computed(() => result.status.value === 'pending')
		})
	};

	return {
		useRequest,
		request
	}
}
