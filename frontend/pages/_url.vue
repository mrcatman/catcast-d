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
  import { Component, Vue } from 'nuxt-property-decorator'
  import Channel from '~/types/Channel'
  import { ChannelGetByUrl } from '~/api/modules/channels'
  import LivePlayer from '~/components/layout/channel-page/LivePlayer.vue'

  @Component({
    components: { LivePlayer },
  })
  export default class ChannelPage extends Vue {

    channel: Channel = {} as Channel;
    error = null;
    loaded: boolean = false

    async fetch() {
      try {
        this.channel = await ChannelGetByUrl(this.$route.params.url);
        this.loaded = true;
      } catch (e) {
        console.log(e);
      }
    }
  }
</script>
