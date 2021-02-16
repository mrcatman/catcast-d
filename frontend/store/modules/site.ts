import { getterTree, mutationTree, actionTree } from 'nuxt-typed-vuex'
import { SiteGetFrontendConfig } from '~/api/modules/site'

export const state = () => ({
  config: null as any
})

export const getters = getterTree(state, {

})

export const mutations = mutationTree(state, {
  setConfig(state, config: any) {
    state.config = config;
  },
})

export const actions = actionTree(
  { state, getters, mutations },
  {
    async initialize({ commit }) {
      let config = await SiteGetFrontendConfig();
      commit('setConfig', config);
    },
  }
)
