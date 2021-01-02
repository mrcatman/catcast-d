import { getterTree, mutationTree, actionTree } from 'nuxt-typed-vuex'
import User from '~/types/User'
import { AuthGetMe, AuthLogout } from '~/api/modules/auth'

export const state = () => ({
  me: null as User | null,
  isLoggedIn: false
})

export const getters = getterTree(state, {

})

export const mutations = mutationTree(state, {
  setMe(state, user: User | null) {
    state.isLoggedIn = (user !== null);
    state.me = user
  },
})

export const actions = actionTree(
  { state, getters, mutations },
  {
    async initialize({ commit }) {
      let user = await AuthGetMe();
      commit('setMe', user);
    },
    async setUser({ commit }, user: null) {
      commit('setMe', user);
    },
    async logout({ commit }) {
      await AuthLogout();
      commit('setMe', null);
    },
  }
)
