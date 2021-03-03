<template>
  <div class="container page-form">
    <h1 class="controls-page__heading">{{$t('dashboard.broadcast._title')}}</h1>
    <ChannelBroadcast :channel="channel" />
  </div>
</template>
<script lang="ts">
import Vue, {PropType} from 'vue';
import Channel from '~/types/Channel'
import ChannelBroadcast from '~/components/layout/dashboard/ChannelBroadcast.vue'
import { UserChannelPermissions } from '~/helpers/permissions'
export default Vue.extend({
  data() {
    return {
      UserChannelPermissions: UserChannelPermissions
    }
  },
  components: { ChannelBroadcast },
  props: {
    channel: Object as PropType<Channel>,
    permissions: Array as PropType<Array<UserChannelPermissions>>
  },
});
</script>
