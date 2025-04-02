namespace Api {

	interface Response {
		message?: string;
	}

	interface Error {
		message?: string;
		errors?: {
			[key: string]: string[];
		}
	}

	interface PaginatedQuery {
		page: number;
		[key: string]: any
	}


	interface PaginatedResponse<T> {
		current_page: number,
		data: T[],
		from: number,
		last_page: number,
		links: any, //todo
		per_page: number,
		to: number,
		total: number
	}

	interface Endpoints {
		Get: {}
		Post: {}
		Put: {},
		Delete: {}
	}

	type ApiMethod = keyof Api.Endpoints
	type ApiUrl<M extends ApiMethod> = keyof Api.Endpoints[M]
	type RequestOptions<M extends ApiMethod, U extends ApiUrl<M>> =
		M extends 'GET'
			? { params?: Api.Endpoints[M][U]['query'] }
			: { body: Api.Endpoints[M][U]['body'] }

	type ResponseType<M extends ApiMethod, U extends ApiUrl<M>> =
		Api.Endpoints[M][U]['response']
}
