<template>
  <div class="container">
    <div class="channel-page">
      <m-box>
        <LivePlayer :channel="channel" />
        <ChannelInfo :channel="channel" :permissions="permissions" />
      </m-box>
      <StreamsList :channel="channel" />
    </div>
  </div>
</template>
<script lang="ts">
  import { Component, Vue, Prop } from 'vue-property-decorator'
  import Channel from '~/types/Channel'
  import LivePlayer from '~/components/layout/channel-page/LivePlayer.vue'
  import ChannelInfo from '~/components/layout/channel-page/ChannelInfo.vue'
  import StreamsList from '~/components/layout/channel-page/StreamsList.vue'
  import { ChannelPermissions } from '~/helpers/channelPermissions'

  @Component({
    components: { ChannelInfo, LivePlayer, StreamsList },
  })
  export default class ChannelPage extends Vue {
    @Prop({required: true}) readonly channel!: Channel
    @Prop({required: true}) readonly permissions!: Array<ChannelPermissions>
  }
</script>
<style lang="scss">
  .channel-page {

  }
</style>
