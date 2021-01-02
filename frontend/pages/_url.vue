<template>
  <div class="channel-page">
    <LivePlayer :channel="channel" v-if="loaded" />
  </div>
</template>
<style lang="scss">
  .channel-page {

  }
</style>
<script lang="ts">
  import Vue from 'vue';
  import Channel from '~/types/Channel'
  import { ChannelGetByUrl } from '~/api/modules/channels'
  import LivePlayer from '~/components/layout/channel-page/LivePlayer.vue'
  export default Vue.extend({
    components: { LivePlayer },
    data() {
      return {
        channel: {} as Channel,
        error: null,
        loaded: false
      }
    },
    async fetch() {
      try {
        this.channel = await ChannelGetByUrl(this.$route.params.url);
        this.loaded = true;
      } catch (e) {
        console.log(e);
      }
    },
  });
</script>
