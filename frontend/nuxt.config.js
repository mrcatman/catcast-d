import { API_URL } from "./constants/urls";

module.exports = {
  mode: 'spa',
  server: {
    host: "0.0.0.0"
  },
  head: {
    titleTemplate: (title) => {
      if (!title) {
        return 'Catcast';
      }
      return `${title} | Catcast`;
    },
    meta: [
      {charset: 'utf-8'},
      {name: 'viewport', content: 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'},
      {hid: 'description', name: 'description', content: 'catcast.tv - live broadcasting and radio'}
    ],
    link: [
      {rel: 'icon', type: 'image/x-icon', href: '/favicon.ico'}
    ],
  },

  loading: {color: '#107fd0'},
  loadingIndicator: 'assets/loading.html',
  plugins: [
    {src: '~/plugins/api.js'},
    {src: '~/plugins/i18n.js'},
    {src: '~/plugins/initialize.js'},
    {src: '~/plugins/vue-echo.js'},
    {src: '~/plugins/dragula.js'},
    {src: '~/plugins/global-components.js'},
    {src: '~/plugins/global-directives.js'},
  ],
  modules: [
    '@nuxtjs/axios',
    '@nuxtjs/pwa',
  ],
  pwa: {
    manifest: {
      name: 'Catcast',
      lang: 'ru',
      useWebmanifestExtension: false,
      theme_color: '#107fd0'
    }
  },
  css: [
    './assets/styles/global.scss',
  ],
  axios: {
    baseURL: API_URL,
    headers: {
      'Content-Type': 'application/json',
    },
    credentials: true,
  },
  build: {
    transpile: [
      'mediasoup-client',
      '@viselect/vanilla'
    ],
    extend(config, {isDev}) {
      if (isDev && process.client) {
        config.module.rules.push({
          enforce: 'pre',
          test: /\.(js|vue)$/,
          loader: 'eslint-loader',
          exclude: /(node_modules)/
        })
        config.module.rules.push({
          test: require.resolve('video.js'),
          use: [{
            loader: 'expose-loader',
            options: 'videojs'
          }]
        })
      }
    }
  },
  router: {
    extendRoutes(routes, resolve) {
      routes.forEach(route => {
        if (route.name === 'dashboard-id') {
          route.children.push(
            {
              name: 'dashboard-id-media-folder',
              path: 'media/folder/:folder_id',
              component: resolve(__dirname, 'pages/dashboard/_id/media/index.vue'),
              chunkName: 'pages/dashboard/_id/media-folder'
            },
          )
        }
        if (route.name === 'directory-id') {
          route.children = [
            {
              name: 'directory-id-sub1',
              path: ':sub1',
              component: resolve(__dirname, 'pages/directory/_id.vue'),
              chunkName: 'pages/directory/_id',
              children: [
                {
                  name: 'directory-id-sub2',
                  path: ':sub2',
                  component: resolve(__dirname, 'pages/directory/_id.vue'),
                  chunkName: 'pages/directory/_id',
                  children: [
                    {
                      name: 'directory-id-sub3',
                      path: ':sub3',
                      component: resolve(__dirname, 'pages/directory/_id.vue'),
                      chunkName: 'pages/directory/_id'
                    },
                  ]
                },
              ]
            },
          ]
        }
      })
      routes.unshift({
        name: 'directory-index',
        path: '/',
        component: resolve(__dirname, 'pages/directory/_id.vue'),
        chunkName: 'pages/directory/_id',
      })
    }
  },
  vue: {
    config: {
      productionTip: false,
      devtools: true
    }
  },
  watchers: {
    webpack: {
      poll: true
    }
  }
}
