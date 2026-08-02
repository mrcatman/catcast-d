export const loadLocaleMessages = async (ctx) => {
  const locales = await ctx.$api.get('locales');

  locales.list.forEach(locale => {
    ctx.app.i18n.setLocaleMessage(locale, {
      code: locale
    });
  })

  let currentLocale = localStorage.getItem('cc_current_locale');
  if (!currentLocale || !locales.list.contains(currentLocale)) {
    currentLocale = locales.default;
  }
  const messages = await ctx.$api.get(`../assets/locales/${currentLocale}.json?ts=${new Date().getTime()}`, {
    axios: {
      withCredentials: false
    }
  });
  ctx.app.i18n.setLocaleMessage(currentLocale, messages);
}
