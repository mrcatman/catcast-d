<template>
  <ChannelsList :title="$t('channels.lists.all')" :full="true" :fn="ChannelsGetOnline" />
</template>

<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { ChannelsGetOnline } from '~/api/modules/channels'
import ChannelsList from '~/components/layout/lists/ChannelsList.vue'

@Component({
  components: {
    ChannelsList,
  },
})
export default class IndexPage extends Vue {
  ChannelsGetOnline = ChannelsGetOnline;
}
</script>

