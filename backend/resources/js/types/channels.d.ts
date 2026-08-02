import type { ChannelType } from "~/constants/entity-types";

namespace Channels {
	interface ItemBase {
		id: number;
		logo: string;
		name: string;
		shortname: string;
	}

	interface Item {
		id: number;
		logo: string;
		name: string;
		shortname: string;
	}

	interface ListQuery extends Api.PaginatedQuery {
		with_permissions?: boolean,
		type?: ChannelType
	}
}

namespace Api {
	interface Endpoints {
		Get: {
			'channels': {
				response: Api.PaginatedResponse<Channels.Item>
				query: Channels.ListQuery
			}
		}
	}
}
