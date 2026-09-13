<template>
  <media-list type="videos" :title="title" />
</template>
<style lang="scss">

</style>
<script>
  import MediaList from '@/components/MediaList';
  export default {
    data() {
      return {
        title: this.$t('videos.new')
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
