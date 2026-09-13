<template>
  <div class="player-page" ref="page">
    <media-player ref="player" :media="media" :channel="channel" class="tooltips-container" />
  </div>
</template>
<script>
  import MediaPlayer from '@/components/media-player/MediaPlayer';
  export default {
    layout: 'empty',
    components: {
      MediaPlayer,
    },
    async asyncData({app,params}) {
      const media = (await app.$api.get(`/media/${params.id}`));
      const channel = (await app.$api.get(`/channels/${video.channel_id}`));
      return {
        media,
        channel,
      }
    }
  }
</script>
