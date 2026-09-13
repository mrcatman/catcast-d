namespace Rating {

	interface Item {
		created_at: string;
		entity_id: number;
		entity_type: string;
		id: number;
		user: Users.Item;
		user_id: number;
		weight: number;
	}

	interface State {
		current_user_has_liked: boolean;
		current_user_like_weight: 1 | 0 | -1;
		last_likes: Item[];
		negative_rating: number;
		positive_rating: number;
		rating: number;
		rating_enabled: boolean;
	}

	interface Body {
		entity_type: Entities.EntityType;
		entity_id: number;
		state: boolean;
		weight: 1 | -1;
	}
}
