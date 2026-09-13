namespace Broadcasts {

	interface Item {
		id: number;
		channel_id: number;
		user_id?: number;
		title?: string;
		description?: string;
		category_id?: number;
		category?: { id: number, name: string };
		tags: string[];
		views?: number;
		viewers?: number;
		is_online: boolean;
		playback_url: string;
		thumbnail_url: string;
		watch_url?: string;
		will_start_at?: string | null;
		will_end_at?: string | null;
		started_at?: string | null;
		ended_at?: string | null;
		can_edit?: boolean;
		can_delete?: boolean;
		can_view_statistics?: boolean;
		user?: Pick<Users.Item, 'id' | 'username'>;
		created_at: string;
		updated_at: string;
	}

	interface Metadata {
		title?: string;
		description?: string;
		category_id?: number;
		tags: string[];
		will_start_at?: string | null;
		will_end_at?: string | null;
	}

	interface CreateBody extends Metadata {
		channel_id?: number;
	}

	type ListFilter = 'all' | 'planned' | 'finished';

	interface ListQuery extends Api.PaginatedQuery {
		show?: ListFilter;
		search?: string;
	}

	interface StreamKey {
		key: string;
		full_key: string;
		[key: string]: any;
	}

	interface StreamServer {
		id: string | number;
		name?: string;
		full_address: string;
		[key: string]: any;
	}

	interface StreamKeyQuery {
		generate_new_key?: boolean;
	}
}

namespace Api {
	interface Endpoints {
		Get: {
			'channels/:id/broadcasts': {
				response: Api.PaginatedResponse<Broadcasts.Item>
				query: Broadcasts.ListQuery
			},
			'/broadcasts/:id': {
				response: Broadcasts.Item
				query: never
			},
			'/channels/:id/broadcasts/active': {
				response: Broadcasts.Item | null
				query: never
			},
			'/channels/:id/stream/key': {
				response: Broadcasts.StreamKey
				query: Broadcasts.StreamKeyQuery
			},
			'/channels/:id/stream/servers': {
				response: Broadcasts.StreamServer[]
				query: never
			}
		}
		Post: {
			'broadcasts': {
				response: Broadcasts.Item
				body: Broadcasts.CreateBody
			}
		}
	}
}
