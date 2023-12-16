<template>
  <channels-list :title="title" queryParams="order=likes_count&type=tv"/>
</template>
<style lang="scss">

</style>
<script>
  import ChannelsList from '@/components/ChannelsList';
  export default {
    data() {
      return {
        title: this.$t('channels.best')
      }
    },
    head() {
      return {
        title: this.title
      }
    },
    components: {
      ChannelsList
    },
  }
</script>
