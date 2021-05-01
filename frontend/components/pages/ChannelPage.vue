<template>
  <div class="channel-page">
    <div class="channel-page__main">
      <m-box>
        <LivePlayer :channel="channel" />
        <ChannelInfo :channel="channel" :permissions="permissions" />
      </m-box>
      <StreamsList :channel="channel" />
    </div>
    <div class="channel-page__chat-toggle" v-if="!chatVisible">
      <m-button @click="chatVisible = true" flat rounded icon="chat"/>
    </div>
    <div class="channel-page__chat" :class="{'channel-page__chat--visible': chatVisible}">
      <div class="channel-page__chat__title">
        {{$t('chat._title')}}
        <m-button @click="chatVisible = false" flat rounded icon="close"/>
      </div>
      <Chat :channel="channel" :permissions="permissions" />
    </div>
  </div>
</template>
<script lang="ts">
  import { Component, Vue, Prop } from 'vue-property-decorator'
  import Channel from '~/types/Channel'
  import LivePlayer from '~/components/layout/channel-page/LivePlayer.vue'
  import ChannelInfo from '~/components/layout/channel-page/ChannelInfo.vue'
  import Chat from '~/components/layout/channel-page/Chat.vue'
  import StreamsList from '~/components/layout/channel-page/StreamsList.vue'
  import { UserChannelPermissions } from '~/helpers/permissions'

  @Component({
    components: { ChannelInfo, Chat, LivePlayer, StreamsList },
  })
  export default class ChannelPage extends Vue {
    @Prop({required: true}) readonly channel!: Channel
    @Prop({required: true}) readonly permissions!: Array<UserChannelPermissions>

    chatVisible: boolean = false;
  }
</script>
<style lang="scss">
/*
.channel-page {
  --box-color: rgb(20 92 214 / 61%);
  --box-footer-color: rgb(13 47 105 / 80%);
  background: url("https://endtimeheadlines.org/wp-content/uploads/2020/06/shutterstock_295846730.jpg") no-repeat center center;
  background-size: cover;

}
 */

  .channel-page {
    display: flex;
    height: 100%;
    overflow: hidden;
    padding: 0 1em;
    @media screen and (max-width: 768px) {
      padding: 0;
      overflow: auto;
    }
    &__main {
      overflow: auto;
      flex: 1;
      margin: 0 .5em 0 0;
      @media screen and (max-width: 768px) {
        margin: 0;
        overflow: visible;
      }
    }
    &__chat {
      width: 30%;
      &__title {
        display: none;
        @media screen and (max-width: 768px) {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: .25em .75em;
          background: var(--box-header-color);
          font-weight: bold;
          font-size: 1.125em;
        }
      }


      @media screen and (max-width: 768px) {
        border: 1px solid var(--active-color);
        box-sizing: border-box;
        position: fixed;
        left: 0;
        bottom: 100%;
        z-index: 100000;
        width: 100%;
        &--visible {
          bottom: 0;
        }
      }
    }
    &__chat-toggle {
      display: none;
      @media screen and (max-width: 768px) {
        display: block;
        position: fixed;
        font-size: 1.25em;
        left: .5em;
        bottom: .5em;
        z-index: 100000000;
      }
    }

  }
</style>
