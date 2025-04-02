<template>
  <div class="page-container profile-page">
    <profile-top :user="data.user" :access-settings="data.accessSettings" />
    <nuxt-page :user="data.user" :access-settings="data.accessSettings" class="profile-page__content"/>
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
<script setup lang="ts">
import ProfileTop from '@/components/profile/top';
const { request } = useApi();
const { params } = useRoute();

const { data, status } = await useAsyncData('user', async () => {
  const user = await request.get(`/users/:id`,{}, {id: params.id});
  const accessSettings = await request.get(`/access-settings/:entityType/:entityId`, {}, {
    entityType: 'users',
    entityId: params.id
  });

  return { user, accessSettings }
})

</script>
