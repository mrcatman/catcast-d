<template>
  <MediaList :title="title" searchParams="order=most_watched"/>
</template>
<style lang="scss">

</style>
<script>
  import MediaList from '@/components/MediaList.vue';
  export default {
    data() {
      return {
        title: this.$t('videos.most_watched')
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
