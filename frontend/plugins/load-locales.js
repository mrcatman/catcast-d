import locales from './locales'
import Vue from 'vue'
import VueI18n from 'vue-i18n'
Vue.use(VueI18n)

export default async ({app}) => {
  app.i18n = new VueI18n({
    locale: 'ru',
    fallbackLocale: 'ru',
  })
  Object.keys(locales).forEach(lang => {
    app.i18n.setLocaleMessage(lang, locales[lang]);
  });
}
