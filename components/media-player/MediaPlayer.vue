<template>
  <custom-colors class="media-player__container" :class="containerClasses"  :colors-scheme="design.colors_scheme">
    <standard-modal v-if="!insidePage" />
    <div class="media-player" @mousedown="onPlayerMouseDown" @dragstart="onPlayerDragStart" :id="'tv_player_'+channel.id" :class="playerClasses" @mousemove="hovered = true" @mouseenter="hovered = true" @mouseleave="hovered = false" ref="container">

      <div v-if="media && !mediaSource" class="media-player__not-ready">
        {{$t('player.upload_not_ready')}}
      </div>

      <!--
<c-modal v-model="panels.password.visible" :header="$t('player.password._title')">
  <div slot="main">
    <div class="modal__input-container">
      <c-input :errors="panels.password.errors.password" :title="$t('player.password.input_title')" v-model="panels.password.data.password"/>
    </div>
  </div>
  <div class="modal__buttons" slot="buttons">
    <div class="buttons-row">
      <c-button :loading="panels.password.loading" @click="checkPassword()">{{$t('global.ok')}}</c-button>
      <c-button flat @click="panels.password.visible = false">{{$t('global.cancel')}}</c-button>
    </div>
  </div>
</c-modal>


<c-modal :inline="!detached" v-model="panels.nsfw.visible" :header="$t('player.adult_content._title')">
  <div slot="main">
    <div class="modal__text">{{$t('player.adult_content._text')}}</div>
    <div class="modal__input-container">
      <c-checkbox :title="$t('player.adult_content.save')"  v-model="panels.nsfw.data.save"/>
    </div>
  </div>
  <div class="modal__buttons" slot="buttons">
    <div class="buttons-row">
      <c-button @click="checkNSFWContent()">{{$t('global.ok')}}</c-button>
      <c-button flat @click="panels.nsfw.visible = false">{{$t('global.cancel')}}</c-button>
    </div>
  </div>
</c-modal>
-->



      <video ref="video" class="video-js media-player__el"></video>

      <media-player-logo v-if="!isVOD && isVideo && videoDimensions" :logo="logo" :videoDimensions="videoDimensions" />

      <div class="media-player__resizer" @mousedown="onPlayerResize" v-if="detached"></div>
      <div class="media-player__background" v-show="!playing && waitingForFirstClick" :style="backgroundElementStyle"></div>

      <!--<media-player-timetable v-if="panels.timetable.visible && !detached" v-model="panels.timetable" /> -->

      <div class="media-player__big-play-button" v-show="waitingForFirstClick && isVideo && !error" @click="tryToPlay()">
        <c-icon icon="play_arrow" />
      </div>

      <media-player-error v-if="error" :error="error" />

      <media-player-top-panel
        :channel="channel"
        :media="media"
        :design="design"
        :broadcast="broadcast"
        :detached="detached"
        :startingSoon="startingSoon"
      />

      <div class="media-player__controls">
        <div class="media-player__controls__outer">
          <div class="media-player__controls__background"></div>
          <media-player-progress-bar v-if="isVOD" :media="media" :duration="duration" :progress="progress" @setTime="setTime" />
          <div class="media-player__controls__inner">
            <div class="media-player__controls__left">
              <media-player-button :icon="playing ? 'pause' : 'play_arrow'" @click="playing ? pause() : tryToPlay()" />
              <div class="media-player__time" v-if="isVOD">
                {{ formatDuration(currentTime) }} /  {{ formatDuration(duration) }}
              </div>
              <div class="media-player__volume">
                <media-player-button :icon="settings.muted ? 'volume_off' : 'volume_up'" @click="setMuted(!settings.muted)" :tooltip="settings.muted ? $t('player.muted.off') : $t('player.muted.on')" />
                <media-player-volume-bar v-show="!settings.muted" v-model="settings.volume" />
              </div>
              <!--<a @click="changeTimetableItemIndex(timetable.videoIndex - 1)" v-if="timetable.playingVideo && timetable.videoIndex > 0" class="media-player__button">
                <i class="material-icons">fast_rewind</i>
              </a>
              <a @click="changeTimetableItemIndex(timetable.videoIndex + 1)" v-if="timetable.playingVideo && timetable.videoIndex + 1 < timetable.playlist.length" class="media-player__button">
                <i class="material-icons">fast_forward</i>
              </a>
              -->
            </div>

            <div class="media-player__controls__right">

              <media-player-settings v-if="isVOD" v-model="settings" :media="media" />
              <media-player-viewers :broadcast="broadcast" />
              <media-player-button v-if="isVideo" @click="setFullscreen(!settings.fullscreen)" :icon="settings.fullscreen ? 'fullscreen_exit' : 'fullscreen'" :tooltip="settings.fullscreen ? $t('player.fullscreen.off') : $t('player.fullscreen.on')" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </custom-colors>

</template>
<script>
import {mapGetters} from "vuex";

  import videojs from 'video.js';
  window.videojs = videojs;
  const videojsHlsjsSourceHandler = require('@streamroot/videojs-hlsjs-plugin');
  videojsHlsjsSourceHandler.register(videojs);
  import { initVideoJsHlsJsPlugin, Engine } from 'p2p-media-loader-hlsjs'

  import {requestFullScreen, cancelFullScreen} from "./functions"; // todo: move
  import { formatDuration } from '@/helpers/dates';

  import CustomColors from "@/components/CustomColors";
  import StandardModal from "@/components/StandardModal";

  import MediaPlayerTimetable from "@/components/media-player/MediaPlayerTimetable";
  import MediaPlayerButton from "@/components/media-player/MediaPlayerButton";
  import MediaPlayerVolumeBar from "@/components/media-player/MediaPlayerVolumeBar";
  import MediaPlayerLogo from "@/components/media-player/MediaPlayerLogo";
  import MediaPlayerTopPanel from "@/components/media-player/MediaPlayerTopPanel";
  import MediaPlayerProgressBar from "@/components/media-player/MediaPlayerProgressBar";
  import MediaPlayerError from "@/components/media-player/MediaPlayerError";
  import MediaPlayerViewers from "@/components/media-player/MediaPlayerViewers";
  import MediaPlayerSettings from "@/components/media-player/MediaPlayerSettings";

  import {CHANNEL_TYPE_TV, CHANNEL_TYPE_RADIO, MEDIA_TYPE_VIDEO, MEDIA_TYPE_AUDIO} from "@/constants/entity-types";
import MediaPlayerWaiting from "@/components/media-player/MediaPlayerWaiting.vue";

  let getBroadcastInterval;
  let disableHoverTimeout;

  export default {
    components: {
      MediaPlayerWaiting,
      StandardModal,
      CustomColors,

      MediaPlayerViewers,
      MediaPlayerError,
      MediaPlayerProgressBar,
      MediaPlayerTopPanel,
      MediaPlayerLogo,
      MediaPlayerVolumeBar,
      MediaPlayerButton,
      MediaPlayerTimetable,
      MediaPlayerSettings
     },
    computed: {
      ...mapGetters('config', ['webtorrentTrackers']),
      type() {
        if (this.channel) {
          return this.channel.type_name;
        } else if (this.media) {
          return this.media.type_name;
        }
        return '';
      },
      playerClasses() {
        return [
          `media-player--${this.type}`,
          this.hovered || this.isAudio ? 'media-player--hovered' : '',
          this.detached ? 'media-player--detached' : '',
        ]
      },
      containerClasses() {
        return [
          `media-player__container--${this.type}`,
          this.insidePage ? 'media-player__container--inside-page' : ''
        ]
      },
      design() {
        return this.playlist ? this.playlist : this.channel;
      },
      backgroundElementStyle() {
        const backgroundImage = this.media?.thumbnail?.full_url || this.design.player_background;
        return {
          backgroundColor: this.design.colors_scheme?.inside_background,
          backgroundImage: backgroundImage ? `url('${backgroundImage}')` : null,
          backgroundSize: 'contain'
        }
      },
      mediaSource() {
        if (this.isVOD && this.vodSource) {
          return {
            type: this.vodSource.mime_type,
            src: this.vodSource.playback_url
          }
        } else if (this.broadcast) {
          return {
            type: 'application/x-mpegURL',
            src: this.broadcast.playback_url
          };
        }
        return null;
      },

      isVOD() {
        return !!this.media;
      },
      isVideo() {
        return this.media && this.media.type_name === MEDIA_TYPE_VIDEO || this.channel.type_name === CHANNEL_TYPE_TV;
      },
      isAudio() {
        return this.media && this.media.type_name === MEDIA_TYPE_AUDIO || this.channel.type_name === CHANNEL_TYPE_RADIO;
      }
    },
    watch: {
      detached(isDetached) {
        let player = this.$refs.container;
        if (!player) {
          return;
        }
        if (!isDetached) {
          player.style.left = 0;
          player.style.bottom = "";
          player.style.top = 0;
          player.style.width = "100%";
          player.style.height = "100%";
        } else {
          player.style.left = "4.5em";
          player.style.top = "";
          player.style.bottom = ".75em";
          player.style.width = "24em";
          player.style.height = "15em";
        }
      },
      async media(newMedia) {
        this.media = newMedia;
        this.startPlayer();
      },
      playing(playing) {
        this.$store.commit('players/SET_IS_PLAYING', {id: this.media ? 'media_' + this.media.id : 'tv_' + this.channel.id, is_playing: playing});
      },

      hovered(hovered) {
        if (hovered) {
          clearTimeout(disableHoverTimeout);
          disableHoverTimeout = setTimeout(()=>{
            this.hovered = false;
          },5500)
        }
      },
      vodSource() {
        this.updateSource();
      },
      'settings.volume'(volume) {
        localStorage.player_volume = volume;
        this.player && this.player.volume(volume / 100);
      },
      'settings.playbackRate'(playbackRate) {
        this.player && this.player.playbackRate(playbackRate);
      }
    },
    beforeDestroy(){
      getBroadcastInterval && clearTimeout(getBroadcastInterval);
      this.player && this.player.dispose();
      this.channel && !this.media && this.$echo.leave(`App.Channel.${this.channel.id}`);
    },
    async mounted() {
      if (this.isVOD && !this.$route.query.autoplay) {
        this.waitingForFirstClick = true;
      }
      await this.initialize();
      if (!this.waitingForFirstClick) {
        this.tryToPlay();
      }
    },
    data() {
      return {
        broadcast: this.channel?.active_broadcast,
        player: null,
        logo: null,
        videoDimensions: null,

        waitingForFirstClick: false,
        startingSoon: false,
        error: null,

        hovered: false,
        progress: 0,
        duration: this.media?.duration,
        currentTime: null,
        playing: false,

        settings: {
          muted: false,
          volume: localStorage.player_volume ? parseInt(localStorage.player_volume) : 75,
          preferredQuality : localStorage.preferred_quality ? parseInt(localStorage.preferred_quality) : null,
          fullscreen: false,
          playbackRate: 1,
        }
      }
    },
    methods: {

      onPlayerResize(e) {
        let resizable = this.$refs.container;
        let startX = e.clientX;
        let startY = e.clientY;
        let startWidth = parseInt(document.defaultView.getComputedStyle(resizable).width, 10);
        let startHeight = parseInt(document.defaultView.getComputedStyle(resizable).height, 10);
        document.documentElement.addEventListener('mousemove', doDrag, false);
        document.documentElement.addEventListener('mouseup', stopDrag, false);

        function doDrag(e) {
          resizable.style.width = (startWidth + e.clientX - startX) + 'px';
          resizable.style.height = (startHeight + e.clientY - startY) + 'px';
        }

        function stopDrag(e) {
          document.documentElement.removeEventListener('mousemove', doDrag, false);
          document.documentElement.removeEventListener('mouseup', stopDrag, false);
        }
      },
      onPlayerMouseDown(e) {
        this.hovered = true;
        if (!this.detached) {
          return;
        }
        if (e.target.className === "media-player__resizer") {
          return;
        }
        let player = this.$refs.container;
        if (!player) {
            return;
        }
        let coords = getCoords(player);
        let shiftX = e.pageX - coords.left;
        let shiftY = e.pageY - coords.top;

        moveAt(e);

        function getCoords(elem) {
          let box = elem.getBoundingClientRect();
          return {
            top: box.top + pageYOffset,
            left: box.left + pageXOffset
          };
        }

        function moveAt(e) {
          player.style.left = e.pageX - shiftX + 'px';
          player.style.top = e.pageY - shiftY + 'px';
        }

        document.onmousemove = function(e) {
          moveAt(e);
        };

        player.onmouseup = function() {
          document.onmousemove = null;
          player.onmouseup = null;
        };
      },
      onPlayerDragStart() {
        if (!this.detached) {
          return;
        }
        return false;
      },
      async checkPassword() { // todo: passwords
        this.passwordWindow.loading = true;
        let data = (await this.$axios.post(`/channels/${this.channel.id}/broadcast`, {password: this.passwordWindow.data.password}));
        this.passwordWindow.loading = false;
        if (!data.status) {
          this.passwordWindow.errors = data.errors;
        } else {
          this.passwordWindow.visible = false;
          this.broadcastState = data;
          localStorage['channel_password_'+this.channel.id] = this.passwordWindow.data.password;
          this.setBroadcast(this.broadcastState);
        }
      },

      setTime(time) {
        if (this.duration && this.player) {
          const wasPaused = this.player.paused();
          this.player.pause();
          this.player.currentTime(time);
          if (!wasPaused) {
            this.player.play();
          }
        }
      },
      permitNSFWContent() {
        if (this.panels.nsfw.data.save) {
          localStorage.allow_nsfw_content = 1;
        }
        this.panels.nsfw.data.visible = false;

        this.tryToPlay();
      },
      checkNSFWContent() {
        let hasNSFWContent = !!this.channel.additional_settings?.display?.has_nsfw_content;
        let hasSavedSetting = localStorage.allow_nsfw_content === "1";
        let skipCheck = !!this.$route.query.skip_nsfw_check;

        if (!skipCheck && (hasNSFWContent && !hasSavedSetting)) {
          this.panels.nsfw.visible = true;
          if (this.player) {
            this.player.pause();
          }
        }
      },
      async play() {

        this.player && this.player.play();
        this.playing = true;
      },
      pause() {
        this.player && this.player.pause();
        this.playing = false;
      },
      setMuted(muted) {
        this.player && this.player.muted(muted);
        this.settings.muted = muted;
      },
      setFullscreen(fullscreen) {
        fullscreen ? requestFullScreen(this.$refs.container) : cancelFullScreen();
        this.settings.fullscreen = fullscreen;
      },
      tryToPlay() {
        if (this.waitingForFirstClick) {
          this.waitingForFirstClick = false;
        }
        if (!this.player) {
          return;
        }

        const promise = this.player.play();
        if (promise !== undefined) {
          promise.then(() => {
            this.play();
          }).catch(() => {
            this.setMuted(true);
            this.play();
          });
        }
      },
      async initialize() {
        if (this.isVOD) {
          this.initVOD();
        } else {
          this.connectSocket();
          this.getLogo();
        }
        await this.initPlayer();
      },
      initVOD() {
        let file = this.media.files.filter(file => file.quality === this.settings.preferredQuality)[0];
        if (!file) {
          file = this.media.files[0];
        }
        this.vodSource = file;
      },

      async initPlayer() {
        return new Promise(resolve => {
          if (this.player) {
            this.player.dispose();
          }

          if (!this.mediaSource) {
            return;
          }
          const engine = new Engine({
            loader: {
              trackerAnnounce: this.webtorrentTrackers
            }
          });
          engine.on("peer_connect", peer => console.log("peer_connect", peer.id, peer.remoteAddress));
          engine.on("peer_close", peerId => console.log("peer_close", peerId));
          engine.on("segment_loaded", (segment, peerId) => console.log("segment_loaded from", peerId ? `peer ${peerId}` : "HTTP", segment.url));

          const playerEl = this.$refs.video;
          const setup = {
            muted: false,
            autoplay: false,
            preload: 'auto',
            techOrder: ['html5'],
            sources: [this.mediaSource],
            controls: false,
            html5: {
              hlsjsConfig: {
                liveSyncDurationCount: 7,
                loader: engine.createLoaderClass()
              }
            }
          }

          initVideoJsHlsJsPlugin();

          this.player = videojs(playerEl, setup);
          this.player.ready(() => {
            this.player.currentTime(0);
            this.player.volume(this.settings.volume);
            this.player.width(playerEl.offsetWidth);
            this.player.height(playerEl.offsetHeight);
            this.player.on('timeupdate', () => {
              this.duration = this.player.duration();
              this.currentTime = this.player.currentTime();
              this.progress = this.currentTime / this.duration;
            });
            this.player.on('error', e => {
              const error = this.player.error();
              console.log(error, this.player);
               if (error.code === 2) { // not started yet
                 this.startingSoon = true;
                 setTimeout(() => {
                   this.startingSoon = false;
                   this.initPlayer();
                 }, 5000)
               } else {
                 this.error = {
                   code: error.code
                 }
               }
            });

            this.player.on('ended', async e => {
              this.$emit('ended');
              // const timetable = this.panels.timetable;
              // if (timetable.playingVideo) {
              //   if (timetable.videoIndex + 1 < timetable.playlist.length) {
              //     this.changeTimetableItemIndex(timetable.videoIndex + 1);
              //   }
              // }
            });
            this.player.on('loadedmetadata', () => {
              const video = document.getElementsByClassName('vjs-tech')[0];
              this.videoDimensions = {
                width: video.videoWidth,
                height: video.videoHeight
              }
              resolve();
            });
          });
          this.checkNSFWContent(); // todo: check before start

          this.player.el_.addEventListener('click', () => {
            this.playing ? this.pause() : this.tryToPlay();
          })
        })
      },
      updateSource() {
        if (!this.player) {
          return;
        }
        const currentTime = this.player.currentTime();
        const wasPaused = this.player.paused();
        this.player.pause();
        this.player.src(this.mediaSource);
        this.player.currentTime(currentTime);
        this.player.playbackRate(this.settings.playbackRate);
        if (!wasPaused) {
          this.player.play();
        }
      },
      async getLogo() {
        this.logo = (await this.$api.get(`channels/${this.channel.id}/logos/active`)).logo;
      },
      connectSocket() {
        this.$echo.join(`App.Channel.${this.channel.id}`)
          .listen('.channel.broadcast_state_changed', async ({broadcast}) => {
            console.log('broadcast_state_changed', broadcast); // todo: show text if broadcast ended
            const isStarting = !broadcast.ended_at && (!this.broadcast || this.broadcast.ended_at);
            const sourceChanged = this.broadcast && this.broadcast.playback_url !== broadcast.playback_url;

            this.broadcast = broadcast;

            if (isStarting) {
              await this.initPlayer();
              this.tryToPlay();
            } else if (sourceChanged) {
              this.updateSource();
            }
          });
      },
      formatDuration,
    },
    props: {
      detached: Boolean,
      insidePage: Boolean,
      page: Object,
      media: Object,
      channel: Object,
      project: Object,
    },
  }
</script>
<style lang="scss">
  .media-player {
    position: relative;
    color: var(--channel-colors-inside-texts);
    width: 100%;
    height: 100%;
    --text-color: var(--channel-colors-inside-texts);
    --box-color: var(--channel-colors-inside-panels);
    overflow: hidden;
    &__container {
      height: 100%;
      &--inside-page {
        height: unset;
        position: relative;
      }
      &--inside-page#{&}--video {
        padding-top: 60%;
      }
      &--inside-page#{&}--audio {
        padding-top: 30%;
      }
    }
    &__container--inside-page & {
      position: absolute;
      top: 0;
      left: 0;
    }

    &__not-ready {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.325em;
      font-weight: 500;
      padding: 0 6em;
      box-sizing: border-box;
      text-align: center;
      line-height: 1.5;
      background: var(--channel-colors-inside-background);
      z-index: 10000000;
    }
    &--detached {
      position: fixed;
      bottom: .75em;
      left: 4.5em;
      z-index: 1000000;
      width: 24em;
      height: 15em;
      font-size: .875em;
      box-shadow: 0 .5em 1em -.5em var(--channel-colors--inside-texts);
    }
    &__resizer {
      position: absolute;
      bottom: -1em;
      right: -1em;
      width: 2em;
      height: 2em;
      cursor: sw-resize;
      z-index: 1000;
    }
    &__big-play-button {
      position: absolute;
      top: calc(50% - 3.125em);
      left: calc(50% - 3.125em);
      width: 6em;
      height: 6em;
      color: var(--channel-colors-inside-buttons);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      cursor: pointer;
      transition: all .4s;
      border: .25em solid;
      &:hover {
        color: var(--channel-colors-inside-buttons-hover);
        transform: scale(1.125);
      }
      i {
        font-size: 2.5em;
      }
    }

    &__viewers {
      margin: 0 .5em 0 0;
      &__count {
        margin: 0 0 0 .25em;
      }
    }



    &__video-container {
      width: 100%;
      height: 100%;
    }

    &__el {
      width: 100%;
      height: 100%;
      background: #000;
      video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
      }
    }
    &__background {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      background-repeat: no-repeat!important;
      background-position: center center!important;
      background-size: contain!important;
    }

    &__controls {
      position: absolute;
      z-index: 1;
      width: 100%;
      left: 0;
      bottom: -4.5em;
      height: 3em;
      transition: all .4s;
      display: flex;
      align-items: center;
      &__left {
        display: flex;
        align-items: center;
      }
      &__outer{
        position: relative;
        width: 100%;
        height: 100%;
      }
      &__background {
        position: absolute;
        left: 0;
        top: -25%;
        background: linear-gradient(rgba(var(--channel-colors-inside-panels--rgb), 0), var(--channel-colors-inside-panels));
        width: 100%;
        height: 125%;
        opacity: .85;
      }
      &__inner {
        padding: 0 .5em;
        position: relative;
        display: flex;
        align-items: center;
        height: 100%;
        justify-content: space-between;
      }
      &__right {
        display: flex;
        align-items: center;
      }
    }



    &--hovered &__controls{
      bottom: 0;
    }

    &--detached &__info__captions {
      font-size: .75em;
    }


    &__volume {
      display: flex;
      align-items: center;
    }
    &__time {
      white-space: nowrap;
      color: var(--channel-colors-inside-texts) !important;
      margin: 0 .5em;
      font-weight: 400;
      font-size: .875em;
    }


    &__timetable-window {
      max-width: 50vw;
      &__header {
        display: flex;
        align-items: center;
      }

      &__picture {
        width: 8em;
        height: 5em;
        background: rgba(0, 0, 0, .5);
        &__question-mark {
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 2.5em;
          font-weight: 600;
        }
      }

      &__texts {
        margin: 0 0 0 .5em;
      }

      &__title {
        font-size: 1.25em;
        font-weight: 500;
      }

      &__info {
        margin: .25em 0;
      }

      &__description {
        padding: 1em 0 0;
      }
      &__button-container {
        margin: 1em 0 0;
      }
    }

    .vjs-control-bar, .vjs-modal-dialog, .vjs-big-play-button {
      display: none;
    }

    .vjs-error-display {
      display: none;
    }
    .vjs-loading-spinner .vjs-control-text {
      display: none;
    }

    .vjs-loading-spinner:before, .vjs-loading-spinner:after {
      border-top-color: var(--channel-colors-inside-buttons);
    }

    .vjs-loading-spinner {
      border-color: var(--channel-colors-inside-buttons);
      opacity: .75;
    }
    .vjs-loading-spinner:before, .vjs-loading-spinner:after{
      animation: vjs-spinner-spin 1.1s cubic-bezier(0.6, 0.2, 0, 0.8) infinite;
    }
  }
</style>
