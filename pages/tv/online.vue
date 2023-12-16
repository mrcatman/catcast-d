<template>
  <channels-list :title="title" queryParams="online=true&order=viewers&type=tv"/>
</template>
<style lang="scss">

</style>
<script>
  import ChannelsList from '@/components/ChannelsList';
  export default {
    data() {
      return {
        title: this.$t('channels.online')
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
