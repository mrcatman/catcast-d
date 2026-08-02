<template>
  <RecordsList :title="title" searchParams="order=popularity"/>
</template>
<style lang="scss">

</style>
<script>
  import RecordsList from '@/components/RecordsList';
  export default {
    data() {
      return {
        title: this.$t('records.popular')
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
