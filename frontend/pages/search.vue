<template>
  <div class="container">
    <m-box>
      <template v-slot:heading>{{$t('search.page_title', {search: $route.query.q})}}</template>
      <template v-slot:default>
        <div class="thumbs-list">
          <ChannelThumb :channel="channel" :key="channel.id" v-for="channel in channels.list" />
        </div>
      </template>
    </m-box>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'

import { ChannelsSearch } from '~/api/modules/channels'
import ChannelThumb from '~/components/layout/thumbs/Channel.vue'
import Channel from '~/types/Channel'
import { Watch } from '~/node_modules/vue-property-decorator'

@Component({
  components: {
    ChannelThumb,
  },
})
export default class SearchPage extends Vue {
  channels = {} as Paginator<Channel>

  @Watch('$route')
  onRouteChange() {
    if (this.$route.query.q) {
      this.load();
    }
  }

  async fetch() {
    this.load();
  }

  async load() {
    this.channels = await ChannelsSearch(this.$route.query.q as string);
  }

}
</script>

<style>

</style>
