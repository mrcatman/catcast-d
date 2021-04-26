import { getAccessorType } from 'nuxt-typed-vuex'

import * as auth from './modules/auth'
import * as site from './modules/site'
import * as sidebar from './modules/sidebar'

export const accessorType = getAccessorType({
  modules: {
    auth,
    site,
    sidebar
  },
})

