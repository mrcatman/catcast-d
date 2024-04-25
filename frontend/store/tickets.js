const tickets = {
  namespaced: true,
  state: {
    tickets: {
      count: 0,
      is_loading: false,
      important_messages_window_visible: false,
    }
  },
  mutations: {
    set(state, {unread_count, has_important_messages}) {
      state.tickets.unread_count = unread_count;
      state.tickets.important_messages_window_visible = has_important_messages;
    }
  },
  actions: {
    async get({commit}) {
      let tickets = await this.$api.get('tickets/unread', {
        onError: {
          unread_count: 0,
          has_important_messages: false
        }
      });
      commit('set', tickets);
    }
  },
  getters: {

  }
};
export default tickets;
