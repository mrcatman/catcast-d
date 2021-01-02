<template>
  <div class="dashboard">
    <m-list>
      <m-list-item :to="`/dashboard/${channel.id}/main`" v-for="channel in channels" :key="channel.id">
        <m-list-item-picture :picture="channel.logo" />
        <m-list-item-texts>
          <m-list-item-title>
            {{channel.name}}
          </m-list-item-title>
        </m-list-item-texts>
      </m-list-item>
    </m-list>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue'
  import { ChannelsGetListMy } from '~/api/modules/channels'
  import Channel from '~/types/Channel'


  export default Vue.extend({
    async fetch() {
      this.channels = await ChannelsGetListMy();
    },
    mounted() {

    },
    components: {

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
