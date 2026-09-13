<template>
  <div class="player-page">
    <media-player :channel="channel"  class="tooltips-container"/>
  </div>
</template>
<script>
  import MediaPlayer from "@/components/media-player/MediaPlayer";
  export default {
    layout: 'empty',
    components: {
      MediaPlayer,
    },
    async asyncData({app,params}) {
      const channel = await app.$api.get(`/channels/${params.id}`);
      return {
        channel,
      }
    },
  }
</script>
