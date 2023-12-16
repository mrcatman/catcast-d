<template>
  <channels-list :title="title" queryParams="order=created_at&type=tv"/>
</template>
<style lang="scss">

</style>
<script>
  import ChannelsList from '@/components/ChannelsList';
  export default {
    data() {
      return {
        title: this.$t('channels.new')
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
