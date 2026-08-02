import { get as _get } from 'lodash';
import { defineStore } from "pinia";
import { useApi } from "../composables/useApi";
import { DEFAULT_SITE_LOGO, DEFAULT_SITE_LOGO_SQUARE } from "@/constants/default-appearance";
import { CHANNEL_TYPE_RADIO, CHANNEL_TYPE_TV, type ChannelType } from "@/constants/entity-types";

export const useConfigStore = defineStore('config', () => {

	const {request} = useApi();

	const config = ref<Config.SiteConfig>();

	const siteUrl = ref<string>();
	const siteDomain = ref<string>();
	const siteName = ref<string>();
	const siteLogo = ref<string>();
	const siteLogoSquare = ref<string>();

	const registrationEnabled = ref<boolean>();
	const registrationManual = ref<boolean>();
	const instanceRules = ref<string>();

	const allowedChannelTypes= ref<{
		[key in keyof ChannelType]: boolean
	}>();

	const fetchConfig = async () => {
		config.value = await request.get('config');

		siteUrl.value = _get(config.value, 'urls.app_url') || '';
		siteName.value = _get(config.value, 'appearance.site_name') || 'Catcast';
		siteLogo.value = _get(config.value, 'appearance.site_logo.common') ? `${siteUrl.value}/${_get(config.value, 'appearance.site_logo.common')}` : DEFAULT_SITE_LOGO;
		siteLogoSquare.value = _get(config.value, 'appearance.site_logo.square') ? `${siteUrl.value}/${_get(config.value, 'appearance.site_logo.square')}` : DEFAULT_SITE_LOGO_SQUARE;
		registrationEnabled.value = _get(config.value, 'users.registration_enabled') || false;
		registrationManual.value = _get(config.value, 'users.registration_manual') || false;
		instanceRules.value = _get(config.value, 'users.instance_rules') || '';
		siteDomain.value = _get(config.value, 'urls.app_domain');
		allowedChannelTypes.value = _get(config.value, 'users.allowed_channel_types') || {
			[CHANNEL_TYPE_TV]: false, [CHANNEL_TYPE_RADIO]: false
		};
	}

	const ratingEnableDislikes = (entityType: Entities.EntityType) => {
		return _get(config.value, `rating.enable_dislikes.${entityType}`, false);
	}
	const ratingShowSummarized = (entityType: Entities.EntityType) => {
		return _get(config.value, `rating.show_summarized.${entityType}`, false);
	}
	const ratingShowUsers = (entityType: Entities.EntityType) => {
		return _get(config.value, `rating.show_users.${entityType}`, false);
	}

	// siteLogoSquare(state, getters) {
	// 	return _get(config.value, 'appearance.site_logo.square') ? `${getters.siteURL}/${_get(config.value, 'appearance.site_logo.square')}` : DEFAULT_SITE_LOGO_SQUARE;
	// },
	// siteSmileys(state) {
	// 	return [];
	// },
	// maxCustomSmileysCount(state) {
	// 	return _get(config.value, 'users.quotas.custom_smileys') || 0;
	// },
	// siteDomain(state) {
	// 	return _get(config.value, 'urls.app_domain');
	// },
	//
	// maxSimultaneousUploadsCount(state) {
	// 	return _get(config.value, 'media.max_simultaneous_uploads') || 1;
	// },
	// webtorrentTrackers(state) {
	// 	return _get(config.value, 'webtorrent.trackers') || [];
	// },
	// allowedChannelTypes(state) {
	// 	return _get(config.value, 'users.allowed_channel_types') || {tv: false, radio: false};
	// },
	// registrationEnabled(state) {
	// 	return _get(config.value, 'users.registration_enabled') || false;
	// },
	// registrationManual(state) {
	// 	return _get(config.value, 'users.registration_manual') || false;
	// },
	// instanceRules(state) {
	// 	return _get(config.value, 'users.instance_rules') || '';
	// },
	// privacyPolicy(state) {
	// 	return _get(config.value, 'users.privacy_policy') || '';
	// },
	// ratingEnableDislikes: (state) => (entityType) => {
	// 	return _get(config.value, `rating.enable_dislikes.${entityType}`, false);
	// },
	// ratingShowSummarized: (state) => (entityType) => {
	// 	return _get(config.value, `rating.show_summarized.${entityType}`, false);
	// },
	// ratingShowUsers: (state) => (entityType) => {
	// 	return _get(config.value, `rating.show_users.${entityType}`, false);
	// }

	return {
		config,
		fetchConfig,

		siteName,
		siteDomain,
		siteUrl,
		siteLogo,
		siteLogoSquare,

		allowedChannelTypes,

		registrationEnabled,
		registrationManual,
		instanceRules,

		ratingShowSummarized,
		ratingEnableDislikes,
		ratingShowUsers
	}
})
