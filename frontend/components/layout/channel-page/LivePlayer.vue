<template>
  <div class="player">
    <video ref="video" controls></video>
  </div>
</template>
<script lang="ts">
  let Hls = require('hls.js');
  import Vue, {PropType} from 'vue';
  import { initHlsJsPlayer, Engine, HlsJsEngineSettings } from 'p2p-media-loader-hlsjs'
  import Channel from '~/types/Channel';


  export default Vue.extend({
    name: 'LivePlayer',
    computed: {
      p2pConfig(): HlsJsEngineSettings {
        return {
          segments: {
            swarmId: this.channel.id!.toString()
          },
          loader: {
            trackerAnnounce: ['ws://localhost:8000']
          }
        }
      }
    },
    mounted() {
      let p2pEngine = new Engine(this.p2pConfig);
      let hls = new Hls({
        liveSyncDurationCount: 7,
        loader: p2pEngine.createLoaderClass()
      });

      initHlsJsPlayer(hls);
      hls.attachMedia(this.$refs.video);
      if (this.channel.is_online) {
        hls.loadSource(this.channel.live_url);
      }
    },

    props: {
      channel: {
        type: Object as PropType<Channel>,
        required: true
      }
    }
  });
</script>
