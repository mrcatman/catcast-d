import Vue from 'vue';
let vm = new Vue();
const players = {
  namespaced: true,
  state: {
    list: {},
  },
  mutations: {
    SET_IS_PLAYING(state, {id, is_playing}) {
      if (state.list && state.list[id]) {
        vm.$set(state.list[id], 'is_playing', is_playing);
      }
    },
    SET_DETACH_STATE(state, {id, detached}) {
      if (state.list && state.list[id]) {
        vm.$set(state.list[id], 'detached', detached);
      }
    },
    CLOSE_PLAYER(state, {id,}) {
      vm.$delete(state.list, id);
    },
    ADD_PLAYER(state, data) {
      let id = data.id;
      data.id = null;
      vm.$set(state.list, id, data);
    }
  },
  actions: {
  },
  getters: {

  }
};
export default players;
