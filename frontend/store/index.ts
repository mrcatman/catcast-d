import { getAccessorType } from 'nuxt-typed-vuex'

import * as auth from './modules/auth'

export const accessorType = getAccessorType({
  modules: {
    auth,
  },
})

