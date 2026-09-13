namespace Bans {

	interface UserBanItem {
		id: number;
		user_id: number;
		channel_id: number;
		banned_by: number;
		banned_till: string | null;
		reason: string;
		can_delete: boolean;
		created_at: string;
		updated_at: string;
		user: Pick<Users.Item, 'id' | 'username' | 'avatar'>;
		banned_by_user?: Pick<Users.Item, 'id' | 'username'>;
	}

	interface IPBanItem {
		id: number;
		ip_address: string;
		channel_id: number;
		banned_by: number;
		banned_till: string | null;
		reason: string;
		can_delete: boolean;
		created_at: string;
		updated_at: string;
		banned_by_user?: Pick<Users.Item, 'id' | 'username'>;
	}

	interface UserBanBody {
		user_id?: number | null;
		username?: string;
		reason?: string;
		ban_duration?: number | null;
		banned_till?: number | null;
	}

	interface IPBanBody {
		ip_address: string;
		reason?: string;
		ban_duration?: number | null;
		banned_till?: number | null;
	}

	interface ListQuery extends Api.PaginatedQuery {
		search?: string;
		count?: number;
	}
}

namespace Api {
	interface Endpoints {
		Get: {
			'/channels/:id/bans': {
				response: Api.PaginatedResponse<Bans.UserBanItem>
				query: Bans.ListQuery
			},
			'/channels/:id/ip-bans': {
				response: Api.PaginatedResponse<Bans.IPBanItem>
				query: Bans.ListQuery
			}
		}
		Post: {
			'/channels/:id/bans': {
				response: Bans.UserBanItem
				body: Bans.UserBanBody
			},
			'/channels/:id/ip-bans': {
				response: Bans.IPBanItem
				body: Bans.IPBanBody
			}
		}
		Delete: {
			'/channels/:id/bans/:userId': {
				response: Api.Response
				body: never
			},
			'/channels/:id/ip-bans/:ip': {
				response: Api.Response
				body: never
			}
		}
	}
}
