import { get as _get} from 'lodash';
import {DEFAULT_SITE_LOGO, DEFAULT_SITE_LOGO_SQUARE} from "@/constants/default-appearance";

const config = {
  namespaced: true,
  state: {
    siteConfig: {}
  },
  mutations: {
    set(state, config) {
      state.siteConfig = {
        ...state.siteConfig,
        ...config
      }
    },
  },
  actions: {
    async get({ commit }) {
      const config = await this.$api.get('config');
      commit('set', config);
    },
  },
  getters: {
    siteName(state) {
      return _get(state.siteConfig, 'appearance.site_name') || 'Catcast';
    },
    siteLogo(state, getters) {
      return _get(state.siteConfig, 'appearance.site_logo.common') ? `${getters.siteURL}/${_get(state.siteConfig, 'appearance.site_logo.common')}` : DEFAULT_SITE_LOGO;
    },
    siteLogoSquare(state, getters) {
      return _get(state.siteConfig, 'appearance.site_logo.square') ? `${getters.siteURL}/${_get(state.siteConfig, 'appearance.site_logo.square')}` : DEFAULT_SITE_LOGO_SQUARE;
    },
    siteSmileys(state) {
      return [];
    },
    maxCustomSmileysCount(state) {
      return _get(state.siteConfig, 'users.quotas.custom_smileys') || 0;
    },
    siteDomain(state) {
      return _get(state.siteConfig, 'urls.app_domain');
    },
    siteURL(state) {
      return _get(state.siteConfig, 'urls.app_url');
    },
    maxSimultaneousUploadsCount(state) {
      return _get(state.siteConfig, 'media.max_simultaneous_uploads') || 1;
    },
    webtorrentTrackers(state) {
      return _get(state.siteConfig, 'webtorrent.trackers') || [];
    },
    allowedChannelTypes(state) {
      return _get(state.siteConfig, 'users.allowed_channel_types') || {tv: false, radio: false};
    },
    registrationEnabled(state) {
      return _get(state.siteConfig, 'users.registration_enabled') || false;
    },
    registrationManual(state) {
      return _get(state.siteConfig, 'users.registration_manual') || false;
    },
    instanceRules(state) {
      return _get(state.siteConfig, 'users.instance_rules') || '';
    },
    privacyPolicy(state) {
      return _get(state.siteConfig, 'users.privacy_policy') || '';
    },
    ratingEnableDislikes: (state) => (entityType) => {
      return _get(state.siteConfig, `rating.enable_dislikes.${entityType}`, false);
    },
    ratingShowSummarized: (state) => (entityType) => {
      return _get(state.siteConfig, `rating.show_summarized.${entityType}`, false);
    },
    ratingShowUsers: (state) => (entityType) => {
      return _get(state.siteConfig, `rating.show_users.${entityType}`, false);
    }
  }
};
export default config;
