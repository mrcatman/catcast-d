import { uuid } from "@/helpers/uuid";
import { NOTIFICATION_VISIBILITY_TIME } from "@/constants/notifications";

const notifications = {
  namespaced: true,
  state: {
    list: []
  },
  mutations: {
    setList(state, list) {
      state.list = list;
    }
  },
  actions: {
    close({state, commit}, randomId) {
      const notifications = state.list.filter(notification => {
        return notification.randomId !== randomId;
      });
      commit('setList', notifications);
    },
    create({state, dispatch, commit}, notification) {
      const randomId = uuid();
      notification = {
        ...notification,
        randomId,
      };
      commit('setList', [
        notification,
        ...state.list
      ])
      setTimeout(() => {
        dispatch('close', randomId);
      }, NOTIFICATION_VISIBILITY_TIME);
    },
  },
};
export default notifications;
