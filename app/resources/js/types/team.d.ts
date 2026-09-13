namespace Team {

	type PermissionValue = 0 | 1 | 2;

	interface Permissions {
		owner?: PermissionValue;
		channel_admin?: PermissionValue;
		[key: string]: PermissionValue | undefined;
	}

	interface Member {
		id: number;
		user_id?: number;
		position?: string;
		hidden?: boolean;
		confirmed?: boolean;
		permissions: Permissions;
		full_permissions?: string[];
		can_edit?: boolean;
		can_delete?: boolean;
		user: Pick<Users.Item, 'id' | 'username' | 'avatar'>;
		added_by?: Pick<Users.Item, 'id' | 'username'>;
		created_at?: string;
		updated_at?: string;
	}

	interface MemberBody {
		user?: Pick<Users.Item, 'id' | 'username'>;
		position?: string;
		hidden?: boolean;
		permissions: Permissions;
	}

	interface Permission {
		id: string;
		title: string;
		description: string;
		can_be_added?: boolean;
		can_be_full?: boolean;
	}
}

namespace Api {
	interface Endpoints {
		Get: {
			'permissions': {
				response: Team.Permission[]
				query: never
			},
			'/channels/:id/team': {
				response: Team.Member[]
				query: { all?: boolean, types?: string[] }
			},
			'/channels/:id/team/manager': {
				response: Api.PaginatedResponse<Team.Member>
				query: Api.PaginatedQuery
			}
		}
		Post: {
			'/channels/:id/team': {
				response: Team.Member
				body: Team.MemberBody
			},
			'/channels/:id/team/leave': {
				response: Api.Response
				body: never
			}
		}
		Delete: {
			'/channels/:id/team/:userId': {
				response: Api.Response
				body: never
			}
		}
	}
}
