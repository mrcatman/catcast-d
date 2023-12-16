import Vue from 'vue'
import Vuex from 'vuex'

import auth from '@/store/auth.js';
import modals from '@/store/modals.js';
import notifications from '@/store/notifications.js';
import tickets from '@/store/tickets.js';
import players from '@/store/players.js';
import config from '@/store/config.js';
import uploads from '@/store/uploads.js';

import {uuid} from "@/helpers/uuid";
import {ALERT_VISIBILITY_TIME} from "@/constants/notifications";

Vue.use(Vuex);
const store = () => new Vuex.Store({
  modules: {
    auth,
    modals,
    notifications,
    tickets,
    players,
    config,
    uploads
  },
  state: {
    initialized: false,

    alerts: [],
		sidebarOpened: (typeof localStorage !== 'undefined' && localStorage.sidebarOpened === 'true') || false,

		locale: typeof localStorage.locale !== 'undefined' ? localStorage.locale : 'ru',
		player: {
			visible: false,
			currentTrack: null,
      element: null,
      needLoad: false,
		},
    currentTheme: localStorage.currentTheme || "flat",
	},
	actions: {
    async nuxtClientInit({state, commit, dispatch}) {
      if (state.initialized) {
        return;
      }
      commit('setInitialized');

      // todo: change
      const timezoneOffset = new Date().getTimezoneOffset();
      this.$axios.defaults.headers.common['X-Timezone-Offset'] = timezoneOffset || 0;
      window.__locale__ = "ru";

      await dispatch('config/get');
      await dispatch('auth/loadUser');
    }
  },
	mutations: {
    setInitialized(state) {
      state.initialized = true;
    },
    SET_CURRENT_THEME(state, theme) {
      localStorage.currentTheme = theme;
      state.currentTheme = theme;
    },
    HIDE_PLAYER_WITHOUT_UNLOAD(state) {
      Vue.set(state,'player',{
        visible: false,
      })
    },
    HIDE_PLAYER(state) {
      Vue.set(state,'player',{
        visible: false,
        track: null
      })
    },
    SET_PLAYER_TRACK_WITHOUT_LOAD(state, {track}) {
      Vue.set(state,'player',{
        visible: true,
        track,
        needLoad: false
      })
    },
    SET_PLAYER_TRACK_ELEMENT(state, {track, element}) {
      document.body.appendChild(element);
      Vue.set(state,'player',{
        visible: true,
        track,
        element
      })


    },
		SET_PLAYER_TRACK(state,track) {
			Vue.set(state,'player',{
				visible: true,
				track: track,
        needLoad: true,
			})
		},

    SET_LOCALE(state, locale) {
      state.locale = locale;
    },
		TOGGLE_SIDEBAR (state) {
			localStorage.sidebarOpened = !state.sidebarOpened;
			state.sidebarOpened = !state.sidebarOpened;
		},

		NEW_ALERT(state, data) {
			let id = Math.floor(Math.random() * 10000);
			state.alerts.push({
				no_translate: data.no_translate,
				text: data.text,
				status: data.status,
				id
			});
			setTimeout(()=>{
				state.alerts = state.alerts.filter(notification=>{
          return notification.id !== id;
        });
			}, ALERT_VISIBILITY_TIME);
		}
	}
});

export default store
