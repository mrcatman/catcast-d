<template>
  <div class="container page-form">
    <m-box>
      <ChannelCommon />
    </m-box>
  </div>
</template>
<script lang="ts">
  import Vue from 'vue';
  import ChannelCommon from '~/components/layout/dashboard/ChannelCommon.vue'

  export default Vue.extend({
    components: {
      ChannelCommon
    }
  })
</script>
