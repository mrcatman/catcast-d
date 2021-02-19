<template>
  <div class="container thumbs-list__outer">
    <div class="thumbs-list__title">
      {{title}}
      <m-button flat v-if="link" class="thumbs-list__title__button" :to="link">{{$t('channels.lists.see_all')}}</m-button>
    </div>
    <div class="thumbs-list">
      <div class="thumbs-list__empty" v-if="channels.total === 0">{{$t('channels.lists.empty')}}</div>
      <ChannelThumb :channel="channel" :key="channel.id" v-for="channel in channels.list" />
    </div>
    <div v-if="full"  class="thumbs-list__pager-container">
      <m-pager v-model="currentPage" :data="channels" />
    </div>

  </div>

</template>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { Watch } from 'vue-property-decorator'

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
    },
    full: {
      type: Boolean,
      required: false,
    },
  },
  components: {
    ChannelThumb,
  },
})
export default class ChannelsList extends Vue {
  currentPage: number = 1;

  @Watch('currentPage')
  async onPageChange(page: number) {
    this.channels = await this.fn(page);
  }

  async fetch() {
    this.channels = await this.fn();
  }
  channels = {} as Paginator<Channel>
}
</script>
