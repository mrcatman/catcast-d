import {defineConfig} from "eslint/config";

export default defineConfig({
	root: true,

	extends: [
		'eslint:recommended',
		'plugin:vue/vue3-essential',
		'plugin:vue/vue3-strongly-recommended',
		'@vue/eslint-config-typescript',
	],

	parserOptions: {
		ecmaVersion: 'latest',
		ecmaFeatures: {
			jsx: true,
		},
	},

	rules: {
		'no-console': process.env.NODE_ENV === 'production' ? ['warn', {allow: ['error']}] : 'off',
		'comma-dangle': ['error', 'always-multiline'],
		camelcase: ['error', {ignoreDestructuring: true, properties: 'never'}],
		semi: ['error', 'always'],
		quotes: ['error', 'single'],
		'array-bracket-spacing': ['error', 'always'],
		'space-before-function-paren': ['error', {
			anonymous: 'never',
			named: 'never',
			asyncArrow: 'always',
		}],
		'valid-typeof': ['error', {requireStringLiterals: false}],
		'vue/require-default-prop': 'off',
		'vue/multi-word-component-names': 'off',
		'vue/no-setup-props-destructure': 'off',
		'no-undef': 'off',
		'@typescript-eslint/no-unused-vars': ['error'],
	},
});
