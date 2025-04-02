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
	vite: {
		server: {
			hmr: {
				port: 4001
			}
		}
	},
	typescript: {
		typeCheck: true
	},
	ssr: false,
	runtimeConfig: {
		public: {
			dev: process.dev,
			apiUrl: process.env.API_URL
		}
	}
})
