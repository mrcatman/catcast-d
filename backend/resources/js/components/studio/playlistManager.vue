<template>
  <div class="playlist-manager">
    <div class="playlist-manager__header">
      <div class="playlist-manager__header__row">
        <div class="playlist-manager__buttons">
          <a @click="changeIndex(-1)" class="playlist-manager__button" :class="{'playlist-manager__button--inactive': currentIndex === 0}">
            <span class="playlist-manager__button__icon">
              <i class="material-icons">skip_previous</i>
            </span>
          </a>
          <a @click="playPause()" class="playlist-manager__button">
            <span class="playlist-manager__button__icon" v-if="paused">
              <i class="material-icons" >play_arrow</i>
            </span>
            <span class="playlist-manager__button__icon" v-else>
              <i class="material-icons" >pause</i>
            </span>
          </a>
          <a @click="changeIndex(1)" class="playlist-manager__button" :class="{'playlist-manager__button--inactive': currentIndex === data.length - 1}">
            <span class="playlist-manager__button__icon">
              <i class="material-icons">skip_next</i>
            </span>
          </a>
          <a @click="cycled = !cycled" class="playlist-manager__button" :class="{'playlist-manager__button--active': cycled}">
            <span class="playlist-manager__button__icon">
              <i class="material-icons">repeat</i>
            </span>
          </a>
        </div>
        <div class="playlist-manager__edit">
          <c-button @click="showPlaylistEditor()" icon-only icon="cached"/>
        </div>
      </div>
      <div class="playlist-manager__header__row">
        <div class="playlist-manager__slider-container">
          <c-slider ref="track_bar" v-if="duration > 0" v-model="sliderPercent"/>
        </div>
        <div class="playlist-manager__time">
          {{ formatDuration(currentTime) }} /  {{ formatDuration(duration) }}
        </div>
        <div class="playlist-manager__volume">
          <span class="playlist-manager__volume__icon">
            <i class="material-icons">volume_up</i>
          </span>
          <div class="playlist-manager__volume__bar">
            <c-slider v-model="volumePercent"/>
          </div>
        </div>
      </div>
    </div>
    <draggable v-model="data" class="playlist-manager__videos">
      <div class="playlist-manager__video" v-if="item" :class="{'playlist-manager__video--active': currentIndex === $index}" :key="$index" v-for="(item, $index) in data">
        <span class="playlist-manager__video__title">{{item.title}}</span>
        <span class="playlist-manager__video__time">{{formatDuration(item.length)}}</span>
      </div>
    </draggable>

    <c-modal v-model="playlistEditor.visible">
      <div slot="main">
        <playlistPicker :channel="channel" v-model="playlistEditor.data"/>
      </div>
      <div class="modal__buttons" slot="buttons">
        <c-button @click="savePlaylist()">{{$t('global.ok')}}</c-button>
      </div>
    </c-modal>

  </div>
</template>
<script>
  import draggable from 'vuedraggable';
  import playlistPicker from '@/components/studio/PlaylistPicker';
  import { formatDuration } from '@/helpers/dates';
  export default {
    beforeDestroy() {
      if (this.video) {
        this.video.pause();
        this.video.removeAttribute('src');
        this.video.load();
      }
    },
    components: {
      draggable,
      playlistPicker
    },
    watch: {
      volumePercent(newPercent) {
          if (this.video) {
              this.video.volume = newPercent;
          }
      },
      sliderPercent(newPercent) {
          if (this.video) {
              this.video.currentTime = this.duration * newPercent;
          }
      }
    },
    data() {
      return {
        noItems: false,
        playlistEditor: {
          visible: false,
          data: []
        },
        volumePercent: 1,
        audioConnected: false,
        video: null,
        currentIndex: 0,
        data: this.value,
        paused: true,
        cycled: false,
        duration: 0,
        currentTime: 0,
        sliderPercent: 0,
      }
    },
    props: {
      channel: {
        required: true,
      },
      value: {
        required: true,
      },
      object: {
        required: true,
      }
    },
    mounted() {
      this.video =  this.object.video;
      if (this.video) {
        this.setVideo();
        this.initVideo();
      } else {
        this.noItems = true;
      }
    },
    methods: {
      initVideo() {
        this.video.crossOrigin = "anonymous";
        this.video.onloadedmetadata = (e) => {
          this.duration = this.video.duration;
          if (!this.paused) {
            // this.video.play();
          }
          this.object.videoActive = true;
          this.object.audioActive = true;
        };
        this.video.ontimeupdate = () => {
          if (this.$refs.track_bar) {
            this.currentTime = this.video.currentTime;
            this.$refs.track_bar.setValue(this.video.currentTime / this.video.duration);
          }
        };
        this.video.onended = () => {
          this.onEnded();
        };

        this.video.onloadeddata = (e) => {
          //this.video.play();
          this.video.volume = this.volumePercent;
          this.object.videoActive = true;
          if (!this.audioConnected) {
            let src = this.object.audioCtx.createMediaElementSource(this.video);
            this.object.audio = src;
            src.connect(this.object.audioCtx.destination);
            this.audioConnected = true;
            this.object.onLoadedAudio();
          }
        }
      },
      savePlaylist() {
        this.data =  JSON.parse(JSON.stringify(this.playlistEditor.data));
        this.playlistEditor.visible = false;
        if (this.noItems && this.data.length > 0) {
          this.noItems = false;
          this.initVideo();
          this.setVideo();

        }
      },
      showPlaylistEditor() {
        this.playlistEditor.data = JSON.parse(JSON.stringify(this.data));
        this.playlistEditor.visible = true;
      },
      onEnded() {
        if (this.currentIndex + 1 <= this.data.length - 1) {
          this.currentIndex++;
          this.setVideo();
        } else {
          if (this.cycled) {
            this.currentIndex = 0;
            this.setVideo();
          }
        }
      },
      setVideo() {
        let currentVideo = this.data[this.currentIndex];
        if (currentVideo) {
          this.video.src = "https://" + currentVideo.server + "/videos/" + currentVideo.url;
          if (this.$refs.track_bar) {
            this.$refs.track_bar.setValue(0);
          }
          if (!this.paused) {
            this.$nextTick(() => {
              this.video.currentTime = 0;
              this.$nextTick(() => {
                this.video.play();
              })
            });
          }
        }
      },
      changeIndex(dir) {
        if (this.currentIndex + dir >=0 && this.currentIndex + dir <= this.data.length - 1) {
          this.currentIndex+=dir;
          this.setVideo();
        }
      },
      playPause() {
        if (this.video) {
          if (this.video.paused) {
            this.paused = false;
            this.video.play();
          } else {
            this.paused = true;
            this.video.pause();
          }
        }
      },
      formatDuration
    }
  }
</script>
<style lang="scss">
  .playlist-manager {
    &__header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-direction: column;
      padding: .5em;
      &__row {
        flex: 1;
        display: flex;
        align-items: center;
        width: 100%;
        justify-content: space-between;
        padding: .5em;
      }
    }

    &__time {
      font-size: 1em;
      font-weight: 600;
    }

    &__video {
      padding: .5em;
      display: flex;
      justify-content: space-between;
      &--active {
        background: var(--active-color);
      }

      &__time {
        font-weight: 600;
      }
    }
    &__buttons {
      margin: 0;
    }
    &__button {
      margin: 0 .5em 0 0;
      background: rgba(255, 255, 255, .05);
      padding: .5em .5em .25em;
      display: inline-flex;
      align-items: center;
      border-radius: var(--border-radius);
      cursor: pointer;
      &:hover {
        background: rgba(255, 255, 255, .1);
      }
      &--inactive {
        cursor: default;
        opacity: .25;
      }
      &--active, &--active:hover {
        background: var(--active-color);
      }
    }
    &__slider-container {
      flex: 1;
      margin: 0 1em 0 .75em;
    }

    &__volume {
      display: flex;
      align-items: center;
      flex: .5;
      margin: 0 .75em 0 2.5em;
      &__bar {
        flex: 1;
      }

      &__icon {
        margin: .25em .5em 0 0;
      }
    }
  }


</style>
