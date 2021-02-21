<template>
  <ChannelPage v-if="loaded" :channel="channel" :permissions="permissions" />
</template>

<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'
  import Channel from '~/types/Channel'
  import { ChannelGetByUrl, ChannelGetPermissions } from '~/api/modules/channels'
  import ChannelPage from '~/components/pages/ChannelPage.vue'

  @Component({
    components: { ChannelPage },
  })
  export default class ChannelUrlPage extends Vue {
    channel: Channel = {} as Channel;
    permissions = [];
    error = null;
    loaded: boolean = false

    async fetch() {
      try {
        this.channel = await ChannelGetByUrl(this.$route.params.url)
        this.permissions = await ChannelGetPermissions(this.channel.id);
        this.loaded = true;
      } catch (e) {
        console.log(e);
      }
    }
  }
</script>
