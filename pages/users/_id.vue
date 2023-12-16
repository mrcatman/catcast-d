<template>
  <div class="page-container profile-page">
    <profile-top :user="user" :access-settings="accessSettings" />
    <nuxt-child :user="user" :access-settings="accessSettings" class="profile-page__content"/>
  </div>
</template>
<style lang="scss">
.profile-page {
  display: block;
  &__content {
    margin-top: 1em;
  }
}
</style>
<script>
import ProfileTop from '@/components/profile/top';
export default {
  components: {
    ProfileTop
  },
  async asyncData({app, error, params}) {
    try {
      const user = await app.$api.get(`/users/${params.id}`);
      const accessSettings = await app.$api.get(`/access-settings/users/${params.id}`);
      return {
        user,
        accessSettings,
      };
    } catch (e) {
      error({ statusCode: 404 })
    }
  },
}
</script>
