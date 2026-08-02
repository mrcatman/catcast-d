namespace Config {

	interface ColorsScheme {
		page_texts: string;
		page_links: string;
		page_panels: string;
		page_headings: string;
		page_background: string;
		page_buttons: string;
		page_buttons_texts: string;
		inside_panels: string;
		inside_texts: string;
		inside_buttons: string;
		inside_buttons_hover: string;
		inside_buttons_texts: string;
		inside_background: string;
		inside_inputs_texts: string;
	}

	interface SiteConfig {
		appearance: {
			default_colors_scheme: ColorsScheme
			site_name: string,
			site_logo: {
				common: string,
				square: string
			}
		},
		pictures: {
			max_file_size: number,
			max_width: number
		},
		comments: {
			max_children_to_load: number
		},
		rating: {
			enable_dislikes: {
				comments: boolean,
				media: boolean
			},
			show_summarized: {
				comments: boolean,
				media: boolean
			},
			show_users: {
				channels: boolean,
				media: boolean
			}
		},
		media: {
			max_simultaneous_uploads: number,
			video: {
				encode_qualities: number[],
				reencode_original_file: boolean,
				store_original_file: boolean,
				enable_hls: boolean
			},
			audio: {
				encode_qualities: number[],
				reencode_original_file: boolean,
				store_original_file: boolean
			}
		},
		statistics: {
			session_duration_seconds: number,
			storage_days: number,
			store_countries: boolean,
			geoip_database: string
		},
		webtorrent: {
			trackers: string[]
		},
		users: {
			registration_enabled: boolean,
			registration_manual: boolean,
			allowed_channel_types: {
				tv: boolean,
				radio: boolean
			},
			quotas: {
				count: {
					tv: number,
					radio: number
				},
				disk: {
					tv: number,
					radio: number
				},
				custom_smileys: number
			},
			instance_rules: string;
			privacy_policy: string;
		},
		bot_public_urls: {
			telegram: string;
			vk: string;
		},
		welcome: {
			title: string;
			description: string;
		}
		urls: {
			app_url: string;
			app_domain: string;
			broadcast: {
				hls_url: string;
				rtmp_url: string;
				rtmp_app_name: string;
			}
		}
	}
}

namespace Api {
	interface Endpoints {
		Get: {
			'config': {
				response: Config.SiteConfig
				query: never
			}
		}
	}

}
