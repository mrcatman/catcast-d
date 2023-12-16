<template>
  <MediaList :title="title" searchParams="order=recommended"/>
</template>
<style lang="scss">

</style>
<script>
  import MediaList from '@/components/MediaList.vue';
  export default {
    data() {
      return {
        title: this.$t('videos.popular')
      }
    },
    head() {
      return {
        title: this.title
      }
    },
    components: {
      MediaList
    },
  }
</script>
