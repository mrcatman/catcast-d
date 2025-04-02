namespace Comments {

	interface Body {
		title: string;
		text: string;
		attachments: any;
		from_channel_name?: boolean;
		reply_to_comment_id?: number;
	}

	interface Item {
		id?: number;
		title: string;
		text: string;
		attachments: any; //todo
		from_channel_name?: boolean;
		children: Comments.Item[];
		children_count: number;

		rating?: Rating.State;

		entity_id?: number | string;
		entity_type?: Props.EntityType;
	}
}

namespace Api {
	interface Endpoints {
		Get: {
			'/:entityType/:entityId/comments': {
				response: PaginatedResponse<Comments.Item>,
			},
			'/comments/:id/children': {
				response: Comments.Item[];
			}
		}
		Post: {
			'/comments': {
				response: Comments.Item
				body: Comments.Body
			},
			'/comments/:id/restore': {
				response: Comments.Item
				body: never
			}
		}
		Put: {
			'/comments/:id': {
				response: Comments.Item
				body: Comments.Body
			}
		},
		delete: {
			'/comments/:id': {
				response: Comments.Item
				body: never
			}
		}
	}

}
