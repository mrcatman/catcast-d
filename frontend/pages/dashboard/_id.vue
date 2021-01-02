<template>
  <div class="dashboard-page__outer">
    <div class="dashboard-page" v-if="loaded">
      <nuxt-child :channel="channel"></nuxt-child>
    </div>
  </div>
</template>
<style lang="scss">
  .dashboard {

  }
</style>
<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'

  import Channel from '~/types/Channel'
  import { ChannelGetById, ChannelGetRights } from '~/api/modules/channels'

  @Component({
    middleware: 'auth',
  })
  export default class DashboardContainerPage extends Vue {

    channel: Channel = {} as Channel;
    rights: Array<string> = [] as Array<string>;
    error = null;
    loaded: boolean = false;

    async fetch() {
      try {
        let id = parseInt(this.$route.params.id);
        this.rights = await ChannelGetRights(id);
        this.channel = await ChannelGetById(id);
        this.loaded = true;
      } catch (e) {
        console.log(e);
      }
    }
  }
</script>
