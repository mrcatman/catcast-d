import { mutationTree } from 'nuxt-typed-vuex'

export const state = () => ({
  opened: localStorage.sidebar_opened !== 'false'
})


export const mutations = mutationTree(state, {
  setOpened(state, opened: boolean) {
    state.opened = opened;
    localStorage.sidebar_opened = opened;
  },
})
