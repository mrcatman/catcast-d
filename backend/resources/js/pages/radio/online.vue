<template>
  <channels-list :title="title" searchParams="online=true&order=viewers&type=radio"/>
</template>
<style lang="scss">

</style>
<script>
  import ChannelsList from '@/components/ChannelsList';
  export default {
    data() {
      return {
        title: this.$t('radio.online')
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
