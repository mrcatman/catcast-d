<template>
  <media-manager :channel="channel" />
</template>
<style lang="scss">

</style>
<script>
  import MediaManager from '@/components/dashboard/MediaManager';
  export default {
    head() {
      return {
        title: this.$t('dashboard.media._title')
      }
    },
    components: {
      MediaManager
    },
    props: {
      channel: {
        type: Object,
        required: true
      }
    },
  }
</script>
