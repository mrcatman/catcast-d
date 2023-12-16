import {loadLocaleMessages} from "@/helpers/locales";

export default async (ctx) => {
  try {
   await loadLocaleMessages(ctx);
  } catch (e) {}
  await ctx.store.dispatch('nuxtClientInit', ctx)
}
