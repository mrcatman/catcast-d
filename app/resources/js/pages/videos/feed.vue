<template>
  <media-list :type="MEDIA_TYPE_VIDEO" :title="title" :searchParams="{show: 'feed'}"/>
</template>
<style lang="scss">

</style>
<script>
  import { MEDIA_TYPE_VIDEO } from "@/constants/entity-types";
  import MediaList from '@/components/MediaList';
  export default {
    data() {
      return {
        title: this.$t('videos.feed'),
        MEDIA_TYPE_VIDEO
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
