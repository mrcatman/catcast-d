<template>
  <div class="container page-form">
    <m-box>
      <m-form-section :title="$t('dashboard.info.common._title')" >
        <ChannelCommon :channel="channel" />
      </m-form-section>
      <m-form-section :title="$t('dashboard.broadcast._title')">
        <ChannelBroadcast :channel="channel" />
      </m-form-section>
    </m-box>
  </div>
</template>
<script lang="ts">
  import Vue, {PropType} from 'vue';
  import Channel from '~/types/Channel'
  import ChannelCommon from '~/components/layout/dashboard/ChannelCommon.vue'
  import ChannelBroadcast from '~/components/layout/dashboard/ChannelBroadcast.vue'
  export default Vue.extend({
    components: { ChannelCommon, ChannelBroadcast },
    props: {
      channel: Object as PropType<Channel>
    },
  });
</script>
