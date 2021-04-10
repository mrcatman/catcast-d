<template>
  <div class="user-info" v-if="user">
    <div class="user-info__top">
      <div class="user-info__avatar" v-if="user.avatar" :style="{backgroundImage: `url(${user.avatar.full_url})`}"></div>
      <div class="user-info__texts">
        <div class="user-info__texts__top">
          <h1 class="user-info__login">{{user.login}}</h1>
          <h2 class="user-info__name">{{user.name}}</h2>
          <a v-if="user.domain" class="user-info__remote-url" target="_blank" :href="`https://${user.domain}/users/${user.login}`">{{user.activitypub_handle}}</a>
          <SubscribeBlock :user="user" />
        </div>
      </div>
    </div>
    <div class="user-info__about">{{user.about}}</div>
  </div>
</template>
<style lang="scss">
  .user-info {
    margin: 1em 0 1.25em;
    position: relative;
    &__top {
      display: flex;
      align-items: flex-start;
    }
    &__avatar {
      margin: 0 1em 0 0;
      background-color: rgba(0, 0, 0, .5);
      width: 7.5em;
      height: 7.5em;
      background-size: contain;
      background-position: center;
      background-repeat: no-repeat;
    }
    &__login {
      font-size: 1.75em;
      margin: 0;
    }
    &__name {
      font-size: 1.325em;
      margin: 0 0 .5em;
      font-weight: 400;
    }
    &__remote-url {
      font-size: .9375em;
    }
    &__texts {

    }

    &__about {
      margin: .5em 0 0;
      line-height: 1.75;
      font-size: 1.25em;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
  }
</style>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { Prop } from '~/node_modules/vue-property-decorator'
import SubscribeBlock from '~/components/layout/channel-page/SubscribeBlock.vue'
import StreamInfoModal from '~/components/layout/channel-page/StreamInfoModal.vue'
import UserLink from '~/components/layout/UserLink.vue'
import User from '~/types/User'

  @Component({
    components: { StreamInfoModal, SubscribeBlock, UserLink },
  })
  export default class ChannelInfoBlock extends Vue {
    streamInfoModalVisible: boolean = false;
    @Prop({default: null}) readonly user!: User
  }
</script>
