import { AuthGetMe } from '~/api/modules/auth'

export default async (ctx) => {
  await ctx.$accessor.modules.auth.initialize();
}
