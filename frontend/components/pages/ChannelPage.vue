<template>
  <div class="container">
    <div class="channel-page">
      <m-box>
        <LivePlayer :channel="channel" />
        <ChannelInfo :channel="channel"/>
      </m-box>
    </div>
  </div>
</template>
<script lang="ts">
  import { Component, Vue, Prop } from 'vue-property-decorator'
  import Channel from '~/types/Channel'
  import LivePlayer from '~/components/layout/channel-page/LivePlayer.vue'
  import ChannelInfo from '~/components/layout/channel-page/ChannelInfo.vue'

  @Component({
    components: { ChannelInfo, LivePlayer },
  })
  export default class ChannelPage extends Vue {
    @Prop({required: true}) readonly channel!: Channel
  }
</script>
<style lang="scss">
  .channel-page {

  }
</style>
