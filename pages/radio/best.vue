<template>
  <channels-list :title="title" searchParams="order=best&type=radio"/>
</template>
<style lang="scss">

</style>
<script>
  import ChannelsList from '@/components/ChannelsList';
  export default {
    data() {
      return {
        title: this.$t('radio.best')
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
