import Vue from 'vue'
import VueI18n from 'vue-i18n'

VueI18n.prototype.getChoiceIndex = function (choice, choicesLength) {
  if (choice === 0) {
    return 0;
  }

  const teen = choice > 10 && choice < 20;
  const endsWithOne = choice % 10 === 1;

  if (!teen && endsWithOne) {
    return 1;
  }

  if (!teen && choice % 10 >= 2 && choice % 10 <= 4) {
    return 2;
  }

  return (choicesLength < 4) ? 2 : 3;
};
Vue.use(VueI18n)

export default ({ app, store }) => {
	app.i18n = new VueI18n({
		locale: store.state.locale,
		fallbackLocale: 'ru',
	})
}
