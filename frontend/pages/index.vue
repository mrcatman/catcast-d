<template>
  <div class="container">
    <div class="thumbs-list">
      <ChannelThumb :channel="channel" :key="channel.id" v-for="channel in channels" />
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'

import { ChannelsGetList } from '~/api/modules/channels'
import ChannelThumb from '~/components/layout/thumbs/Channel.vue'
import Channel from '~/types/Channel'

@Component({
  components: {
    ChannelThumb,
  },
})
export default class IndexPage extends Vue {
  async fetch() {
    this.channels = await ChannelsGetList();
  }
  channels = [] as Array<Channel>
}
</script>

<style>

</style>
