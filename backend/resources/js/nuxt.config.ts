// Modules from the Nuxt 2 app that are not installed in this package.json.
// They are only reached from pages and plugins that have not been ported to
// Vue 3 yet, so the build leaves them unresolved rather than failing. Anything
// importing one will break at runtime until it is reinstated in package.json —
// remove entries here as the corresponding code is ported.
const unportedModules = [
	'@viselect/vanilla',
	'dragula',
	'flatpickr',
	'hacktimer',
	'mediasoup-client',
	'protoo-client',
	'vue-draggable-resizable',
	'vue-flatpickr-component',
	'vue-the-mask',
	'vuedraggable',
	// Vue 2 build entrypoint, imported by the legacy dragula/directive plugins.
	'vue/dist/vue.min.js'
];

const isUnported = (id: string) =>
	unportedModules.some((mod) => id === mod || id.startsWith(`${mod}/`));

// Vue 2 plugins carried over from the Nuxt 2 app. They register globals via
// `Vue.use`/`Vue.directive` and import `vue/dist/vue.min.js`, none of which
// exists in Vue 3, and they expose no `defineNuxtPlugin` default export — so
// Nuxt skips them at runtime but still lists them in the plugin manifest,
// which breaks the build. Delete them or port them to Vue 3.
const legacyPlugins = [
	'plugins/dragula.js',
	'plugins/global-directives.js',
	'plugins/vue2-dragula.js'
];

const isLegacyPlugin = (src: string) =>
	legacyPlugins.some((plugin) => src.replace(/\\/g, '/').endsWith(plugin));

// Pages still named with the Nuxt 2 `_param.vue` convention. Nuxt 3 would map
// them to literal paths (/donate/_id) rather than dynamic routes, so they are
// dead either way, and several import modules or components that no longer
// exist. Dropping them from the route table keeps them out of the bundle.
// Renaming one to `[param].vue` as part of porting it is enough to include it.
const isUnportedPage = (file?: string) => !!file && /[\\/]_[^\\/]+\.vue$/.test(file);

const stripUnportedPages = (pages: { file?: string; children?: any[] }[]) => {
	for (let i = pages.length - 1; i >= 0; i--) {
		if (isUnportedPage(pages[i].file)) {
			pages.splice(i, 1);
			continue;
		}
		if (pages[i].children?.length) {
			stripUnportedPages(pages[i].children!);
		}
	}
};

export default defineNuxtConfig({
	compatibilityDate: '2024-11-01',
	modules: [
		'nuxt3-vuex-module',
		'@nuxtjs/i18n',
		[
			"@pinia/nuxt",
			{
				autoImports: ["defineStore", "acceptHMRUpdate"],
			}
		],
	],
	pinia: {
		storesDirs: ['./stores/**'],
	},
	css: ['~/assets/styles/global.scss'],
	devtools: {enabled: true},
	components: [
		{
			path: '~/components/ui',
			pathPrefix: false,
			prefix: 'c'
		},
		'~/components'
	],
	i18n: {
		strategy: 'no_prefix',
		locales: [
			{
				code: 'en',
				file: './loader.ts'
			},
			{
				code: 'ru',
				file: './loader.ts'
			},
		],
		lazy: true,
		defaultLocale: 'ru'
	},
	hooks: {
		'pages:extend'(pages) {
			stripUnportedPages(pages);
		},
		'app:resolve'(app) {
			app.plugins = app.plugins.filter((plugin) => !isLegacyPlugin(plugin.src));
		}
	},
	// Served by Laravel: assets land in backend/public/_nuxt, the app shell
	// becomes backend/resources/views/spa.html (see scripts/deploy-spa.mjs).
	app: {
		buildAssetsDir: '/_nuxt/'
	},
	devServer: {
		host: '0.0.0.0',
		port: 3000
	},
	nitro: {
		// This is a client-rendered app served by Laravel, so crawling routes
		// only produces copies of the same shell. Build just the entry.
		prerender: {
			crawlLinks: false,
			routes: ['/']
		}
	},
	vite: {
		server: {
			// HMR is reached through the nginx proxy, not the Nuxt port directly.
			hmr: {
				clientPort: Number(process.env.NUXT_HMR_CLIENT_PORT ?? 8080)
			}
		},
		build: {
			rollupOptions: {
				external: (id: string) => isUnported(id)
			}
		}
	},
	typescript: {
		// Re-enable once the remaining Nuxt2 pages are ported to Vue 3.
		typeCheck: false
	},
	ssr: false,
	runtimeConfig: {
		public: {
			dev: process.dev,
			// Same-origin by default now that Laravel serves the SPA.
			apiUrl: process.env.API_URL || '/api/'
		}
	}
})
