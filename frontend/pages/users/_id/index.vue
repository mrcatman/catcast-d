<template>
  <div>
    <div class="profile-page__restricted" v-if="!accessSettings.can_view_profile">
      <div class="profile-page__restricted__inner">
        <div class="profile-page__restricted__text">{{accessSettings.ban ? $t('profile.user_has_banned_you') : $t('profile.restricted')}}</div>
      </div>
    </div>
    <c-row v-else align="start">
      <c-col mobile-full-width :grow="2">
        <profile-info :user="user" />
        <profile-channels :channels="channels" />
      </c-col>
      <c-col mobile-full-width :grow="3">
        <comments-list :access-settings="accessSettings"  entity-type="users" :entity-id="user.id" />
      </c-col>
    </c-row>
  </div>
</template>
<script>
  import ProfileInfo from '@/components/profile/info';
  import ProfileChannels from '@/components/profile/channels';
  import CommentsList from "@/components/comments/CommentsList";
  export default {
    props: {
      user: {
        type: Object,
        required: true
      },
      accessSettings: {
        type: Object,
        required: true
      }
    },
    head() {
      return {
        title: this.user.username
      }
    },
    components: {
      ProfileInfo,
      ProfileChannels,
      CommentsList,
    },
    async asyncData({app, params}) {
      const channels = await app.$api.get(`/users/${params.id}/channels`, {onError: []});
      return {
        channels,
      };
    },
  }
</script>
<style lang="scss">
  .profile-page {
    &__restricted {
      text-align: center;
      font-size: 1.25em;
    }

  }
</style>
