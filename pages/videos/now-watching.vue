<template>
  <MediaList :title="title" searchParams="order=now_watching"/>
</template>
<style lang="scss">

</style>
<script>
  import MediaList from '@/components/MediaList.vue';
  export default {
    data() {
      return {
        title: this.$t('videos.now_watching')
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
