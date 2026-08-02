<template>
  <media-list type="videos" :title="title" searchParams="order=best"/>
</template>
<style lang="scss">

</style>
<script>
  import MediaList from '@/components/MediaList.vue';
  export default {
    data() {
      return {
        title: this.$t('videos.best')
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
