<template>
  <RecordsList :title="title" searchParams="order=now_listening"/>
</template>
<style lang="scss">

</style>
<script>
  import RecordsList from '@/components/RecordsList';
  export default {
    data() {
      return {
        title: this.$t('records.now_listening')
      }
    },
    head() {
      return {
        title: this.title
      }
    },
    components: {
      RecordsList
    },
  }
</script>
