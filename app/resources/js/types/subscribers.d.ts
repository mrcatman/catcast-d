namespace Subscribers {
	interface Item {
		id: number;
		entity_id: number;
		entity_type: string;
		user_id: number;
		weight: number;
		created_at: string;
		updated_at: string;
		user: Pick<Users.Item, 'id' | 'username' | 'avatar'>;
	}
}

namespace Api {
	interface Endpoints {
		Get: {
			'/channels/:id/subscribers': {
				response: Api.PaginatedResponse<Subscribers.Item>
				query: Api.PaginatedQuery
			}
		}
	}
}
