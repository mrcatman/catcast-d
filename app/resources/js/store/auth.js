
const auth = {
  namespaced: true,
  state: {
    loggedIn: false,
    user: null,
  },
  mutations: {
    setUser(state, user) {
      state.user = user;
      state.loggedIn = !!user;
    },
  },
  actions: {
    async loadUser({ commit }) {
      try {
        const user = (await this.$api.get('/auth/me', {
          noAlerts: true
        })).user;
        commit('setUser', user);
      } catch (e) {
        commit('setUser', null);
      }
    },
    logout({ commit }) {
      commit('setUser', null);
    },
    incrementNotificationsCount({ state, commit }) {
      commit('setUser', {
        ...state.user,
        unread_notifications_count: (state.user.unread_notifications_count || 0) + 1
      })
    },
    readNotifications({ state, commit }) {
      commit('setUser', {
        ...state.user,
        unread_notifications_count: 0
      })
    }
  }
};
export default auth;
