namespace Sidebar {
	interface MenuItem {
		heading: string;
		icon?: string;
		url?: string;
		children?: MenuItem[];
	}

	type Menu = MenuItem[];
}



namespace Api {
	interface Endpoints {
		Get: {
			'/directory/menu': {
				response: Sidebar.Menu
			}
		}
	}

}
