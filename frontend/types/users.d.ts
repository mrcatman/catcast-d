namespace Users {


	interface Item {
		about?: string;
		actor_id?: string;
		additional_settings: {
			privacy: {
				can_view_profile: string,
				can_write_on_wall: string
			}
		}
		avatar?: string;
		created_at: string;
		domain?: string;
		full_name?: string;
		id: number;
		inbox_url?: string;
		info: string[];
		is_admin?: boolean;
		key_id?: string;
		last_seen: string;
		links: string[];
		outbox_url?: string;
		public_key: string;
		role_id: number;
		shared_inbox_url?: string;
		status_text?: string;
		unread_notifications_count: number;
		updated_at: string;
		username: string;
		web_url: string;
	}

	interface Channel {
		owner: boolean;
		position?: string;
		channel: Channels.ItemBase;
	}
}


namespace Api {
	interface Endpoints {
		Get: {
			'/users/:id/channels': {
				response: Users.Channel[]
				query: never
			},
		}

	}

}
