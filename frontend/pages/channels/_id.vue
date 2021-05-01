<template>
  <ChannelPage v-if="loaded" :channel="channel" :permissions="permissions" />
</template>

<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'
  import Channel from '~/types/Channel'
  import { ChannelGetById, ChannelGetByUrl, ChannelGetPermissions } from '~/api/modules/channels'
  import ChannelPage from '~/components/pages/ChannelPage.vue'
  import UserPermissions from '~/types/UserPermissions'
  import { UserChannelPermissions } from '~/helpers/permissions'

  @Component({
    components: { ChannelPage },
  })
  export default class ChannelUrlPage extends Vue {
    channel: Channel = {} as Channel;
    permissions: Array<UserChannelPermissions> = [];
    error = null;
    loaded: boolean = false

    async fetch() {
      try {
        this.channel = await ChannelGetById(parseInt(this.$route.params.id));
        this.permissions = await ChannelGetPermissions(parseInt(this.$route.params.id));
        this.loaded = true;
      } catch (e) {
        console.log(e);
      }
    }
  }
</script>
