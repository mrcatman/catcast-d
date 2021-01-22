<template>
  <div class="player" ref="container">
    <div class="player__outer">
      <div class="player__inner">
        <video class="player__video" ref="video"></video>
        <div class="player__bottom">
          <div class="player__bottom__left">
            <a title="Play" class="player__button" @click="state.playing = !state.playing">
              <i class="material-icons player__button__icon">{{state.playing ? 'pause' : 'play_arrow'}}</i>
            </a>
          </div>
          <div class="player__bottom__right">
            <a title="Play" class="player__button" @click="state.fullscreen = !state.fullscreen">
              <i class="material-icons player__button__icon">{{state.fullscreen ? 'fullscreen_exit' : 'fullscreen'}}</i>
            </a>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>
<style lang="scss">
  .player {
    width: 100%;
    padding-top: 56.25%;
    position: relative;
    &__outer {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #000;
    }
    &__inner {
      position: relative;
      width: 100%;
      height: 100%;
    }
    &__video {
      width: 100%;
      height: 100%;
    }
    &__bottom {
      position: absolute;
      bottom: 0;
      padding: 1em;
      display: flex;
      justify-content: space-between;
      width: 100%;
      box-sizing: border-box;
    }
    &__button {
      font-size: 1.75em;
      cursor: pointer;
      transition: opacity .25s;
      height: 1em;
      display: inline-block;
      &__icon {
        font-size: inherit;
      }
      &:hover {
        opacity: .75;
      }
    }
  }
</style>
<script lang="ts">
  import { Component } from '~/node_modules/vue-property-decorator'

  let Hls = require('hls.js');
  import Vue from 'vue';
  import { Watch, Prop } from 'vue-property-decorator'

  import { initHlsJsPlayer, Engine, HlsJsEngineSettings } from 'p2p-media-loader-hlsjs'
  import Channel from '~/types/Channel';
  import { cancelFullScreen, requestFullScreen } from '~/helpers/fullscreen'

  @Component
  export default class LivePlayer extends Vue {

    @Prop({ required: true }) readonly channel!: Channel

    playerInstance: any = null;
    state: any = {
      playing: false,
      fullscreen: false
    }

    $refs!: {
      video: HTMLVideoElement
      container: HTMLElement
    }

    @Watch('state.playing')
    playPause(isPlaying: boolean) {
      isPlaying ? this.$refs.video.play() : this.$refs.video.pause();
    }

    @Watch('state.fullscreen')
    goFullscreen(isFullscreen: boolean) {
      isFullscreen ? requestFullScreen(this.$refs.container) : cancelFullScreen();
    }

    get p2pConfig(): HlsJsEngineSettings {
      return {
        segments: {
          swarmId: this.channel.id!.toString()
        },
        loader: {
          trackerAnnounce: ["wss://tracker.novage.com.ua", "wss://tracker.openwebtorrent.com"] //'ws://localhost:8000'//
        }
      }
    }

    mounted() {
      let p2pEngine = new Engine(this.p2pConfig);
      this.playerInstance = new Hls({
        liveSyncDurationCount: 7,
        loader: p2pEngine.createLoaderClass()
      });

      initHlsJsPlayer(this.playerInstance);
      this.playerInstance.attachMedia(this.$refs.video);

      if (this.channel.is_online && this.channel.current_stream) {
        this.playerInstance.loadSource(this.channel.current_stream.watch_url);
      } 
    }



  }
</script>
