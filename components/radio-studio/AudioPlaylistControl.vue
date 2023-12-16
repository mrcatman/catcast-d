<template>
  <div class="radio-studio__playlist">
    <div class="radio-studio__playlist__controls">
      <div class="radio-studio__playlist__buttons">
        <a @click="changeIndex(-1)" class="radio-studio__playlist__button" :class="{'radio-studio__playlist__button--inactive': currentIndex === 0}">
          <span class="radio-studio__playlist__button__icon">
            <i class="material-icons">skip_previous</i>
          </span>
        </a>
        <a @click="playPause()" class="radio-studio__playlist__button">
          <span class="radio-studio__playlist__button__icon" v-if="paused">
            <i class="material-icons" >play_arrow</i>
          </span>
          <span class="radio-studio__playlist__button__icon" v-else>
            <i class="material-icons" >pause</i>
          </span>
        </a>
        <a @click="changeIndex(1)" class="radio-studio__playlist__button" :class="{'radio-studio__playlist__button--inactive': currentIndex === playlistTracksList.length - 1}">
          <span class="radio-studio__playlist__button__icon">
            <i class="material-icons">skip_next</i>
          </span>
        </a>
        <a @click="cycled = !cycled" class="radio-studio__playlist__button" :class="{'radio-studio__playlist__button--active': cycled}">
          <div class="tooltip">{{$t('radio_studio.cycle_playlist')}}</div>
          <span class="radio-studio__playlist__button__icon">
             <i class="material-icons">repeat</i>
          </span>
        </a>
        <a @click="toggleMetadata()" class="radio-studio__playlist__button tooltip-container" :class="{'radio-studio__playlist__button--active': sendMetadata}">
          <div class="tooltip">{{$t('radio_studio.auto_update_metadata')}}</div>
          <span class="radio-studio__playlist__button__icon">
            <i class="material-icons">info</i>
          </span>
        </a>
      </div>
      <div class="radio-studio__playlist__slider-container">
        <c-slider ref="track_bar" v-if="duration > 0" v-model="sliderPercent"/>
      </div>
      <div class="radio-studio__playlist__time">
        {{ formatDuration(currentTime) }} /  {{ formatDuration(duration) }}
      </div>
    </div>
    <div v-dragula="playlistTracksList" drake="studio_tracks" class="radio-studio__playlist__tracks" :class="{'radio-studio__playlist__tracks--empty': playlistTracksList.length === 0}">
      <div class="radio-studio__playlist__track" v-if="track" :key="$index" v-for="(track, $index) in playlistTracksList">
        <div class="radio-studio__playlist__track__progress" v-if="currentIndex === $index">
          <div class="radio-studio__playlist__track__progress__bar" :style="{width: realPercent * 100 + '%'}"></div>
        </div>
        <div class="radio-studio__playlist__track__play-button" @click="setIndex($index)">
          <i class="fa fa-play"></i>
        </div>
        <div class="radio-studio__playlist__track__info">
          <span class="radio-studio__playlist__track__title">{{track.title}}</span>
          <span class="radio-studio__playlist__track__artist">{{track.author}}</span>
        </div>
        <div class="radio-studio__playlist__track__delete-button" @click="deleteTrack(track, $index)">
          <i class="fa fa-times"></i>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .radio-studio {
    &__playlist {
      &__track {
        padding: .5em;
        position: relative;
        &__info {
          padding: 0 0 0 1.75em;
          position: relative;
          z-index: 1;
        }
        &__artist {
          font-weight: 500;
          margin: 0 0 0 .5em;
        }
        &__play-button {
          z-index: 10;
          opacity: .5;
          cursor: pointer;
          position: absolute;
          top: .5em;
          left: .5em;
          &:hover {
            opacity: .75;
          }
        }
        &__delete-button {
          z-index: 1;
          opacity: .5;
          cursor: pointer;
          position: absolute;
          top: .5em;
          right: .5em;
          &:hover {
            opacity: .75;
          }
        }
        &__progress {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          border-radius: .25em;
          overflow: hidden;
          &__bar {
            background: var(--active-color);
            height: 100%;
          }
        }


      }
      &__tracks {
        margin: 1em;
        background: rgba(0, 0, 0, .25);
        padding: .5em;
        border-radius: .25em;
        &--empty {
          min-height: 5em;
        }
      }


      &__controls {
        display: flex;
        align-items: center;
        margin: 1em 1em 0;
      }

      &__slider-container {
        flex: 1;
        margin: 0 1em;
      }

      &__buttons {
        display: flex;
        align-items: center;
        line-height: .5;
      }

      &__button {
        cursor: pointer;
        background: rgba(255, 255, 255, .1);
        padding: .25em;
        margin: 0 .25em 0 0;
        border-radius: .25em;
        &--active {
          background: var(--active-color);
        }
        &--inactive {
          opacity: .5;
          cursor: default;
        }
      }
    }
  }

</style>
<script>
  import { formatDuration } from '@/helpers/dates';
  export default {
    beforeDestroy() {

      this.audio.pause();
      this.audio.removeAttribute('src');
      this.audio.load();
    },
    watch: {
      playlistTracksList(newList) {
        let data = this.data;
        data.playlist = newList;
        this.$emit('input', data);
      },
      volumePercent(newPercent) {
        this.audio.volume = newPercent;
      },
      sliderPercent(newPercent) {
        this.audio.currentTime = this.duration * newPercent;
      },
      value(newVal) {
        if (newVal.playlist) {
          this.playlistTracksList = newVal.playlist;
        }
      }
    },
    methods: {
      deleteTrack(track, $index) {
        this.playlistTracksList.splice($index, 1);
      },
      toggleMetadata() {
        this.sendMetadata = !this.sendMetadata;
        if (this.playlistTracksList[this.currentIndex]) {
          this.updateMetadata(this.playlistTracksList[this.currentIndex]);
        }
      },
      onEnded() {
        if (this.currentIndex + 1 <= this.data.length - 1) {
          this.currentIndex++;
          this.setTrack();
        } else {
          if (this.cycled) {
            this.currentIndex = 0;
            this.setTrack();
          }
        }
      },
      updateMetadata(track) {
        this.$emit('metadata', {
          title: track.title || "",
          artist: track.author || ""
        })
      },
      setTrack() {
        let track = this.playlistTracksList[this.currentIndex];
        this.audio.crossOrigin = "anonymous";
        this.audio.src = track.client_path;
        if (this.sendMetadata) {
          this.updateMetadata(track);
        }
        if (this.$refs.track_bar) {
          this.$refs.track_bar.setValue(0);
        }
        if (!this.paused) {
          this.$nextTick(() => {
            this.audio.currentTime = 0;
            this.$nextTick(() => {
              this.audio.play();
            })
          });
        }
      },
      setIndex(index) {
        this.currentIndex = index;
        this.setTrack();
      },
      changeIndex(dir) {
        if (this.currentIndex + dir >=0 && this.currentIndex + dir <= this.playlistTracksList.length - 1) {
          this.currentIndex += dir;
          this.setTrack();
        }
      },
      playPause() {
        if (this.audio.paused) {
          this.paused = false;
          this.audio.play();
        } else {
          this.paused = true;
          this.audio.pause();
        }
      },
      formatDuration
    },
    created() {
      const service = this.$dragula.$service;
      service.eventBus.$on('drop', (args) => {
        const el = args.el;
        if (el.parentElement) {
          const index = Array.prototype.indexOf.call(el.parentElement.children, el);
          if (el.dataset.info) {
            const trackData = JSON.parse(el.dataset.info);
            const track = trackData.track;
            el.parentNode.removeChild(el);
            if (track.is_folder || track._is_back_folder) return;
            this.playlistTracksList.splice(index, 0, track);
            this.playlistTracksList = this.playlistTracksList.filter(track => track !== undefined);
            if (!this.started) {
              this.started = true;
              this.setTrack();
            }
          }
        } else {
          console.log(el);
        }
      });
    },
    mounted() {
      if (this.data.el) {
        this.audio = this.data.el;
       this.audio.onloadedmetadata = (e) => {
          this.duration = this.audio.duration;
        };
        this.audio.ontimeupdate = () => {
          if (this.$refs.track_bar) {
            this.currentTime = this.audio.currentTime;
            let realPercent = this.audio.currentTime / this.audio.duration;
            this.$refs.track_bar.setValue(realPercent);
            this.realPercent = realPercent;
          }
        };
        this.audio.onended = () => {
          this.onEnded();
        };
        if (this.playlistTracksList.length > 0) {
          this.setTrack();
        }

      }
    },
    data() {
      return {
        sendMetadata: true,
        realPercent: 0,
        //volumePercent: 1,
        started: false,
        paused: true,
        cycled: false,
        duration: 0,
        currentTime: 0,
        sliderPercent: 0,
        audio: null,
        currentIndex: 0,
        playlistTracksList: [],
        data: this.value
      }
    },
    props: {
      value: {
        type: Object,
        required: true
      }
    }
  }
</script>
