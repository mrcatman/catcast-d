import type { UseFetchOptions } from 'nuxt/app'

export const useApi = () => {

	const {public: {apiUrl}} = useRuntimeConfig()
	const { newAlert } = useAlertsStore();

	const apiRequest = $fetch.create({
		baseURL: apiUrl,
		credentials: 'include',
		onRequest({request, options, error}) {

		},
		onResponse({response}) {
			if (response.status === 200 && response._data.message) {
				newAlert({
					type: 'success',
					text: response._data.message,
				})
			}
		},
		onResponseError({response}) {
			if (response._data.message) {
				newAlert({
					type: 'error',
					text: response._data.message,
				})
			}
		}
	})

	interface Replacements {
		[key: string]: any;
	}

	const createUrl = (url: string, replacements?: Replacements): string => {
		if (!replacements) {
			return url;
		}
		for (const key in replacements) {
			url = url.replace(`:${key}`, replacements[key]);
		}

		return url;
	}

	const request = {
		get: <U extends Api.ApiUrl<'Get'>>(url: U, options?: Api.RequestOptions<'Get', U>, replacements?: Replacements): Promise<Api.ResponseType<'Get', U>> => {
			return apiRequest(createUrl(url, replacements), {
				method: 'GET',
				...options
			})
		},
		post: <U extends Api.ApiUrl<'Post'>>(url: U, options: Api.RequestOptions<'Post', U>, replacements?: Replacements): Promise<Api.ResponseType<'Post', U>> => {
			return apiRequest(createUrl(url, replacements), {
				method: 'POST',
				...options
			})
		},
		put: <U extends Api.ApiUrl<'Put'>>(url: U, options: Api.RequestOptions<'Put', U>, replacements?: Replacements): Promise<Api.ResponseType<'Put', U>> => {
			return apiRequest(createUrl(url, replacements), {
				method: 'PUT',
				...options
			})
		},
		delete: <U extends Api.ApiUrl<'Delete'>>(url: U, options: Api.RequestOptions<'Delete', U>, replacements?: Replacements): Promise<Api.ResponseType<'Delete', U>> => {
			return apiRequest(createUrl(url, replacements), {
				method: 'DELETE',
				...options
			})
		}
	}

	const useRequest = <M extends Api.ApiMethod, U extends Api.ApiUrl<M>>(
		url: U,
		options?: UseFetchOptions<Api.ResponseType<M, U>> & {
			method?: M
		} & Api.RequestOptions<M, U>,
		replacements?: Replacements
	) => {
		return useFetch(createUrl(url as string, replacements), {
			...options,
			method: options?.method || 'GET',
			$fetch: apiRequest
		})
	};

	return {
		useRequest,
		request
	}
}
