import { getAccessorType } from 'nuxt-typed-vuex'

import * as auth from './modules/auth'
import * as site from './modules/site'

export const accessorType = getAccessorType({
  modules: {
    auth,
    site,
  },
})

