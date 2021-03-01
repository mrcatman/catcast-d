<template>
  <div class="channel-page">
    <div class="channel-page__main">
      <m-box>
        <LivePlayer :channel="channel" />
        <ChannelInfo :channel="channel" :permissions="permissions" />
      </m-box>
      <StreamsList :channel="channel" />
    </div>
    <div class="channel-page__chat">
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
  import { UserChannelPermissions } from '~/helpers/UserChannelPermissions'

  @Component({
    components: { ChannelInfo, Chat, LivePlayer, StreamsList },
  })
  export default class ChannelPage extends Vue {
    @Prop({required: true}) readonly channel!: Channel
    @Prop({required: true}) readonly permissions!: Array<UserChannelPermissions>
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
    &__main {
      overflow: auto;
      flex: 1;
      margin: 0 .5em 0 0;
    }
    &__chat {
      width: 30%;
    }
  }
</style>
