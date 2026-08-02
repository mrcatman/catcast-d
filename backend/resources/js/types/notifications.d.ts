namespace Notifications {
	interface Item {
		created_at: string;
		id: string;
		is_read: boolean;
		picture: string;
		text: TextWithParams;
		title: TextWithParams;
		type: string; // todo: notification types
		url: string;
	}
}


namespace Api {
	interface Endpoints {
		Get: {
			'/notifications': {
				response: Api.PaginatedResponse<Notifications.Item>,
			}
		}
		Post: {
			'/notifications/:id/restore': {
				response: Api.Response,
			}
		}
		Delete: {
			'/notifications/:id': {
				response: Api.Response,
			}
		}
	}
}
