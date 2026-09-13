import type { ChannelType } from "~/constants/entity-types";

namespace Channels {
	interface ItemBase {
		id: number;
		logo: string;
		name: string;
		shortname: string;
	}

	interface Smiley {
		id: number;
		code: string;
		full_url?: string;
	}

	interface ForbiddenWord {
		word: string;
	}

	interface ChatSettings {
		allow_guests: boolean;
		default_guest_username: string;
		forbidden_words: ForbiddenWord[];
		disabled: boolean;
		motd: string;
		smileys: Smiley[];
	}

	interface RecordingSettings {
		record_all: boolean;
		records_public: boolean;
	}

	interface AdditionalSettings {
		chat: ChatSettings;
		recording?: RecordingSettings;
		privacy?: Record<string, any>;
		display?: Record<string, any>;
		donates?: Record<string, any>;
		broadcast?: Record<string, any>;
		record?: Record<string, any>;
		default_broadcast_metadata?: Record<string, any>;
		[key: string]: any;
	}

	interface Item extends ItemBase {
		description?: string;
		domain?: string;
		user_id: number;
		channel_type: number;
		type_name: 'tv' | 'radio';
		is_radio: boolean;
		views: number;
		likes_count: number;
		banner?: string;
		background?: string;
		player_background?: string;
		local_url?: string;
		colors_scheme?: string;
		tags: string[];
		links: any[];
		additional_settings: AdditionalSettings;

		blocked_at?: string | null;
		block_reason?: string | null;
		is_banned?: boolean;
		ban_reason?: string;

		permissions?: Record<string, boolean | number>;
		can_leave_team?: boolean;

		last_online_at?: string | null;
		created_at: string;
		updated_at: string;
	}

	interface CreateBody {
		channel_type: ChannelType;
		name: string;
		shortname: string;
		tags: string[];
	}

	interface ListQuery extends Api.PaginatedQuery {
		with_permissions?: boolean,
		type?: ChannelType,
		channel_type?: ChannelType
	}

	interface DetailQuery {
		do_not_count_stat?: boolean;
	}
}

namespace Api {
	interface Endpoints {
		Get: {
			'channels': {
				response: Api.PaginatedResponse<Channels.Item>
				query: Channels.ListQuery
			},
			'/channels/:id': {
				response: Channels.Item
				query: Channels.DetailQuery
			},
			'access-settings/channels/:id': {
				response: { permissions: Record<string, boolean | number> }
				query: never
			}
		}
		Post: {
			'/channels': {
				response: Channels.Item
				body: Partial<Channels.CreateBody>
			}
		}
		Put: {
			'/channels/:id': {
				response: Channels.Item
				body: Partial<Channels.Item>
			}
		}
	}
}
