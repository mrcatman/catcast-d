<template>
  <ChannelsList :title="$t('channels.lists.local')" :full="true" :fn="ChannelsGetLocal" />
</template>

<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { ChannelsGetLocal } from '~/api/modules/channels'
import ChannelsList from '~/components/layout/lists/ChannelsList.vue'

@Component({
  components: {
    ChannelsList,
  },
})
export default class IndexPage extends Vue {
  ChannelsGetLocal = ChannelsGetLocal;
}
</script>

