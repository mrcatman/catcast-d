namespace Playlists {

	interface Picture {
		id: number;
		full_url: string;
	}

	interface PicturesData {
		logo?: Picture;
		banner?: Picture;
		background?: Picture;
		player_background?: Picture;
	}

	interface Item {
		id: number;
		uuid: string;
		channel_id: number;
		user_id?: number;
		name: string;
		description?: string;
		views: number;
		likes_count: number;
		media_count: number;
		privacy_status: number;
		privacy_status_name: string;
		colors_scheme?: string;
		local_url?: string;
		logo?: string;
		pictures_data: PicturesData;
		tags: string[];
		can_edit?: boolean;
		use_custom_design?: boolean;
		links?: Array<{ title?: string, url?: string }>;
		category?: { id: number, name: string };
		media?: Media.Item[];
		created_at: string;
		updated_at: string;
	}

	interface SaveBody extends Partial<Item> {
		media_ids?: number[];
	}

	interface CreateBody {
		name: string;
		privacy_status: number;
		channel_id?: number;
	}

	interface ManagerListQuery extends Api.PaginatedQuery {
		order?: 'new' | 'old' | 'popular';
		search?: string;
	}
}

namespace Api {
	interface Endpoints {
		Get: {
			'channels/:id/playlists/manager': {
				response: Api.PaginatedResponse<Playlists.Item>
				query: Playlists.ManagerListQuery
			},
			'/playlists/:id': {
				response: Playlists.Item
				query: never
			},
			'/channels/:id/playlists/all': {
				response: Playlists.Item[]
				query: never
			}
		}
		Post: {
			'playlists': {
				response: Playlists.Item
				body: Playlists.CreateBody
			}
		}
		Put: {
			'/playlists/:id': {
				response: Playlists.Item
				body: Playlists.SaveBody
			}
		}
		Delete: {
			'playlists/:id': {
				response: Api.Response
				body: never
			}
		}
	}
}
