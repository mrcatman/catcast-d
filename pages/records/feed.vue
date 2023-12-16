<template>
  <RecordsList :title="title" searchParams="order=feed"/>
</template>
<style lang="scss">

</style>
<script>
  import RecordsList from '@/components/RecordsList';
  export default {
    data() {
      return {
        title: this.$t('records.feed')
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
