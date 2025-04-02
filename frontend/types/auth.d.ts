namespace Auth {
	interface Credentials {
		login: string;
		password: string;
	}

	interface UpdatePassword {
		old_password: string;
		new_password: string;
		new_password_confirmation: string;
	}

	interface AccessSettings {
		ban?: boolean;
		can_view_profile?: boolean;
		comments_enabled?: boolean;
		comments_display?: boolean
	}


}

namespace Api {
	interface Endpoints {
		Get: {
			'/access-settings/:entityType/:entityId': {
				response: Auth.AccessSettings
				query: never
			},
			'/auth/me': {
				response: { user?: Users.Item }
				query: never
			}
		}
		Post: {
			'/auth/login': {
				response: Users.Item
				body: Auth.Credentials
			}
			'/auth/password': {
				response: Users.Item
				body: Auth.UpdatePassword
			}
		}
		Put: {
			'/auth/me': {
				response: { user: Users.Item }
				body: Partial<Users.Item>
			}
		}
	}

}
