<template>
<div class="page-container">
  <c-thumbs-list :config="{title: $t('videos.feed'), buttonUrl: '/videos/feed', url: '/media?type=video&show=feed&count=10'}">
    <template slot="item" slot-scope="props">
      <video-thumb :data="props.item" />
    </template>
  </c-thumbs-list>
  <c-thumbs-list :config="{title: $t('videos.new'), buttonUrl: '/videos/new', url: '/media?type=video&count=10'}">
    <template slot="item" slot-scope="props">
      <video-thumb :data="props.item" />
    </template>
  </c-thumbs-list>
</div>
</template>
<script>
  import VideoThumb from '@/components/thumbs/VideoThumb';

  export default {
    head () {
      return {
        title: this.$t('videos.title'),
      }
    },
    components: {
      VideoThumb
    },
  }
</script>
