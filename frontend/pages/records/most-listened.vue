<template>
  <RecordsList :title="title" searchParams="order=most_listened"/>
</template>
<style lang="scss">

</style>
<script>
  import RecordsList from '@/components/RecordsList';
  export default {
    data() {
      return {
        title: this.$t('records.most_listened')
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
