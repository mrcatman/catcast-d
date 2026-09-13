namespace Media {

	interface File {
		id: number;
		quality?: number;
		url?: string;
		download_url?: string;
		[key: string]: any;
	}

	interface Permissions {
		can_edit?: boolean;
		can_delete?: boolean;
		can_view_statistics?: boolean;
	}

	interface Item {
		id: number;
		uuid?: string;
		channel_id: number;
		user_id?: number;
		folder_id?: number | null;
		title: string;
		description?: string;
		thumbnail?: string;
		local_url?: string;
		type?: string;
		duration?: number;
		views?: number;
		likes_count?: number;
		privacy_status: number;
		privacy_status_name?: string;
		category_id?: number;
		category?: { id: number, name: string };
		tags: string[];
		playlist_ids?: number[];
		files: Media.File[];
		permissions: Media.Permissions;
		created_at: string;
		updated_at: string;
	}

	interface DetailQuery {
		load_permissions?: number | boolean;
	}
}

namespace Api {
	interface Endpoints {
		Get: {
			'/media/:id': {
				response: Media.Item
				query: Media.DetailQuery
			}
		}
		Put: {
			'/media/:id': {
				response: Media.Item
				body: Partial<Media.Item>
			}
		}
	}
}
