<template>
  <div class="container">
    <div class="thumbs-list">
      <ChannelThumb :channel="channel" :key="channel.id" v-for="channel in channels" />
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue'
import { ChannelsGetList } from '~/api/modules/channels'
import ChannelThumb from '~/components/layout/thumbs/Channel.vue'
import Channel from '~/types/Channel'

export default Vue.extend({
  async fetch() {
    this.channels = await ChannelsGetList();
  },
  mounted() {

  },
  components: {
    ChannelThumb,

  },
  data() {
    return {
      channels: [] as Array<Channel>
    }
  },

  computed: {

  },

  methods: {

  },
})
</script>

<style>

</style>
