<template>
  <div>
    <div class="profile-page__restricted" v-if="!accessSettings.can_view_profile">
      <div class="profile-page__restricted__inner">
        <div class="profile-page__restricted__text">
          {{ accessSettings.ban ? $t('profile.user_has_banned_you') : $t('profile.restricted') }}
        </div>
      </div>
    </div>
    <c-row v-else align="start">
      <c-col mobile-full-width :grow="2">
        <profile-info :user="user"/>
        <profile-channels :channels="channels" />
      </c-col>
      <c-col mobile-full-width :grow="3">
        <comments-list :access-settings="accessSettings" entity-type="users" :entity-id="user.id"/>
      </c-col>
    </c-row>
  </div>
</template>
<script lang="ts" setup>
import ProfileInfo from '@/components/profile/info';
import ProfileChannels from '@/components/profile/channels';
import CommentsList from "@/components/comments/CommentsList";

const {params} = useRoute();

const { request } = useApi();

const props = defineProps<{
  user: Users.Item,
  accessSettings: Auth.AccessSettings,
}>();

useHead(() => {
  return {
    title: props.user.username
  }
})

const { data: channels } = await useAsyncData( 'user-channels', () => request.get( `/users/:id/channels`, {}, {
  id: params.id,
}));

</script>
<style lang="scss">
.profile-page {
  &__restricted {
    text-align: center;
    font-size: 1.25em;
  }

}
</style>
