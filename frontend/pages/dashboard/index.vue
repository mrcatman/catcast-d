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
  import { Component, Vue } from 'nuxt-property-decorator'

  import { ChannelsGetListMy } from '~/api/modules/channels'
  import ChannelThumb from '~/components/layout/thumbs/Channel.vue'
  import Channel from '~/types/Channel'

  @Component({
    components: {
      ChannelThumb,
    },
  })
  export default class DashboardIndexPage extends Vue {
    async fetch() {
      this.channels = await ChannelsGetListMy();
    }
    channels = [] as Array<Channel>
  }
</script>

<style>

</style>
