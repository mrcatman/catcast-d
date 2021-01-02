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
  import Vue from 'vue';
  import Channel from '~/types/Channel'
  import { ChannelGetById, ChannelGetRights } from '~/api/modules/channels'
  export default Vue.extend({
    middleware: "auth",
    data() {
      return {
        channel: {} as Channel,
        rights: [] as Array<string>,
        error: null,
        loaded: false
      }
    },
    async fetch() {
      try {
        let id = parseInt(this.$route.params.id);
        this.rights = await ChannelGetRights(id);
        this.channel = await ChannelGetById(id);
        this.loaded = true;
      } catch (e) {
        console.log(e);
      }
    },
  });
</script>
