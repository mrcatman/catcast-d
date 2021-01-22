<template>
  <ChannelPage v-if="loaded" :channel="channel" />
</template>

<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'
  import Channel from '~/types/Channel'
  import { ChannelGetById, ChannelGetByUrl } from '~/api/modules/channels'
  import ChannelPage from '~/components/pages/ChannelPage.vue'

  @Component({
    components: { ChannelPage },
  })
  export default class ChannelUrlPage extends Vue {
    channel: Channel = {} as Channel;
    error = null;
    loaded: boolean = false

    async fetch() {
      try {
        this.channel = await ChannelGetById(parseInt(this.$route.params.id));
        this.loaded = true;
      } catch (e) {
        console.log(e);
      }
    }
  }
</script>
