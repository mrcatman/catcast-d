module.exports = {
  mode: 'spa',
  telemetry: false,
  /*
   ** Headers of the page
   */
  head: {
    title: 'Catcast D',
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      {
        hid: 'description',
        name: 'description',
        content: process.env.npm_package_description || '',
      },
    ],
    link: [{ rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }],
  },
  /*
   ** Customize the progress-bar color
   */
  loading: { color: '#fff' },
  /*
   ** Global CSS
   */
  css:[

  ],
  /*
   ** Plugins to load before mounting the App
   */
  plugins: [
    { src: '~/plugins/notifications.js'},
    { src: '~/plugins/load-locales.js'},
    { src: '~/plugins/global-components.js'},
    { src: '~/plugins/portal-vue.js'},
    { src: '~/plugins/init-store.js'},
  ],
  /*
   ** Nuxt.js dev-modules
   */
  buildModules: [
    '@nuxt/typescript-build',
    'nuxt-typed-vuex',
  ],
  /*
   ** Nuxt.js modules
   */
  modules: [
    '@nuxtjs/style-resources',
    '@nuxtjs/dotenv',
    '@nuxtjs/axios',
  ],
  styleResources: {
    scss: [

    ]
  },
  /*
   ** Build configuration
   */
  build: {
    extend(config, ctx) {},
  },

}
