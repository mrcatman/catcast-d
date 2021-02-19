<template>
  <div class="thumbs-list__outer">
    <div class="thumbs-list__title">
      {{title}}
      <m-button flat v-if="link" class="thumbs-list__title__button" :to="link">{{$t('channels.lists.see_all')}}</m-button>
    </div>
    <div class="thumbs-list">
      <div class="thumbs-list__empty" v-if="channels.total === 0">{{$t('channels.lists.empty')}}</div>
      <ChannelThumb  :channel="channel" :key="channel.id" v-for="channel in channels.list" />
    </div>
  </div>

</template>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'

import ChannelThumb from '~/components/layout/thumbs/Channel.vue'
import Channel from '~/types/Channel'
import Paginator from '~/types/Paginator'

@Component({
  props: {
    link: {
      type: String,
      required: false
    },
    title: {
      type: String,
      required: false
    },
    fn: {
      type: Function,
      required: true,
    }
  },
  components: {
    ChannelThumb,
  },
})
export default class IndexPage extends Vue {
  async fetch() {
    this.channels = await this.fn();
  }
  channels = {} as Paginator<Channel>
}
</script>
