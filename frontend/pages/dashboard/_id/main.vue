<template>
  <div class="container page-form">
    <h1 class="controls-page__heading">{{$t('dashboard.info.common._title')}}</h1>
    <ChannelCommon :channel="channel" />
  </div>
</template>
<script lang="ts">
  import Vue, {PropType} from 'vue';
  import Channel from '~/types/Channel'
  import ChannelCommon from '~/components/layout/dashboard/ChannelCommon.vue'
  import { UserChannelPermissions } from '~/helpers/permissions'
  export default Vue.extend({
    data() {
      return {
        UserChannelPermissions: UserChannelPermissions
      }
    },
    components: { ChannelCommon },
    props: {
      channel: Object as PropType<Channel>,
      permissions: Array as PropType<Array<UserChannelPermissions>>
    },
  });
</script>
