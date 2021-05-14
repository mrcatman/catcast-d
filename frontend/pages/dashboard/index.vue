<template>
  <div class="container dashboard-index">
    <m-box no-padding>
      <div slot="heading" class="box__heading__row">
        {{ $t('dashboard.index.my_channels') }}
        <div class="box__heading__buttons">
          <m-button to="/dashboard/create">{{$t('dashboard.index.create_channel')}}</m-button>
        </div>
      </div>
      <m-list>
        <m-list-item :to="`/dashboard/${channel.id}/main`" v-for="channel in channels.list" :key="channel.id">
          <m-list-item-picture :picture="channel.logo" defaultFromConfig="channel_default_logo" />
          <m-list-item-texts>
            <m-list-item-title>
              {{channel.name}}
            </m-list-item-title>
          </m-list-item-texts>
        </m-list-item>
      </m-list>
    </m-box>
  </div>
</template>

<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'

  import { ChannelsGetListMy } from '~/api/modules/channels'
  import ChannelThumb from '~/components/layout/thumbs/Channel.vue'
  import Channel from '~/types/Channel'
  import Paginator from '~/types/Paginator'

  @Component({
    components: {
      ChannelThumb,
    },
  })
  export default class DashboardIndexPage extends Vue {
    async fetch() {
      this.channels = await ChannelsGetListMy();
    }
    channels = {} as Paginator<Channel>
  }
</script>

<style>
.dashboard-index {
  margin-top: 1em;
}
</style>
