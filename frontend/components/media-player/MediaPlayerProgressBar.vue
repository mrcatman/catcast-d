<template>
  <div class="media-player__progress-bar" @mousedown="onStartChangingProgress" ref="progress_bar" @mousemove="onProgressBarMouseMove">
    <div class="media-player__progress-bar__inner">
      <div class="media-player__progress-bar__time-container" :style="{left: hoverProgressPercent + '%', marginLeft: (gridThumbnailData  ? ((gridThumbnailData.width / -2) + 'px') : (hoverOffset + 'px'))}">
        <div ref="hover_time" class="media-player__progress-bar__time" :style="{marginTop: (gridThumbnailData ? ((-1 * gridThumbnailData.height) + 'px') : null)}" v-if="hoverTime">
          <div class="media-player__progress-bar__thumbnail" ref="grid_thumbnail" v-if="gridThumbnailData" :style="gridThumbnailElementStyle"></div>
          <div class="media-player__progress-bar__time__text">{{hoverTime}}</div>
        </div>
      </div>
      <div class="media-player__progress-bar__handle-container">
        <div ref="handle" class="media-player__progress-bar__handle" :style="{left: (realProgress * 100) + '%'}"></div>
      </div>
      <div class="media-player__progress-bar__value-container">
        <div class="media-player__progress-bar__value" :style="{width: (realProgress * 100) + '%'}"></div>
      </div>
    </div>
  </div>
</template>
<script>
import { formatDuration} from "@/helpers/dates";

export default {
  data() {
    return {
      hoverProgressPercent: 0,
      hoverOffset: null,
      isChangingProgress: false,
      realProgress: this.progress || 0,
    }
  },
  watch: {
    progress(progress) {
      if (!this.isChangingProgress) {
        this.realProgress = progress;
      }
    }
  },
  computed: {
    gridThumbnailData() {
      if (this.media.grid_thumbnail && this.media.grid_thumbnail.picture && this.duration) {
        const thumbnail = this.media.grid_thumbnail;
        const second = (this.hoverProgressPercent / 100) * this.duration;
        const index = Math.floor(second / thumbnail.every_nth_second);
        const height = 85;
        const width = thumbnail.width * height / thumbnail.height;
        return {
          url: thumbnail.picture.full_url,
          position_x: -1 * index * width,
          width,
          height,
          background_size_x: width * thumbnail.frames_count,
          background_size_y: height
        };
      }
      return null;
    },
    gridThumbnailElementStyle() {
      let offsetLeft = 0;
      const progressBarWidth = this.$refs.progress_bar?.clientWidth;
      const timeLeftPosition = this.hoverProgressPercent / 100 * progressBarWidth;
      const gridThumbnailHalfWidth = this.$refs.grid_thumbnail?.clientWidth / 2;
      if (timeLeftPosition < gridThumbnailHalfWidth) {
        offsetLeft = gridThumbnailHalfWidth - timeLeftPosition;
      } else if (timeLeftPosition > progressBarWidth - gridThumbnailHalfWidth) {
        offsetLeft = progressBarWidth - gridThumbnailHalfWidth - timeLeftPosition;
      }
      return {
        marginLeft: `${offsetLeft}px`,
        backgroundImage: `url('${this.gridThumbnailData.url}')`,
        backgroundSize: `${this.gridThumbnailData.background_size_x}px ${this.gridThumbnailData.background_size_y}px`,
        width: `${this.gridThumbnailData.width}px`,
        height: `${this.gridThumbnailData.height}px`,
        backgroundPosition: `${this.gridThumbnailData.position_x}px 0`
      }
    },
    hoverTime() {
      if (this.duration) {
        return formatDuration((this.hoverProgressPercent / 100) * this.duration);
      }
      return null;
    },
  },
  methods: {
    setTime() {
      const time = this.hoverProgressPercent * this.duration / 100;
      this.$emit('setTime', time);
    },
    onStartChangingProgress(e) {
      this.isChangingProgress = true;
      let onMouseMove = () => {
        this.realProgress = this.hoverProgressPercent / 100;
      };
      onMouseMove(e);
      window.addEventListener('mousemove', onMouseMove);
      let alreadySetTime = false;
      window.addEventListener('mouseup', () => {
        if (!alreadySetTime) {
          this.setTime();
          setTimeout(() => {
            this.isChangingProgress = false;
          }, 1000);
          window.removeEventListener('mousemove', onMouseMove);
          alreadySetTime = true;
        }
      })
    },
    onProgressBarMouseMove(e) {
      const bar = this.$refs.progress_bar;
      const rect = bar.getBoundingClientRect();
      const width = rect.width;
      const left = rect.left;
      const handleWidth = this.$refs.handle.clientWidth;
      let x = e.clientX - (handleWidth / 2);
      let percent = (x - left) / width * 100;
      if (percent > 100) {
        percent = 100;
      }
      if (percent < 0) {
        percent = 0;
      }
      this.hoverProgressPercent = percent;
      if (!this.media.grid_thumbnail && this.$refs.hover_time) {
        this.hoverOffset = this.$refs.hover_time.clientWidth / -2;;
      }
    },
  },
  props: {
    media: {
      type: Object,
      required: true
    },
    duration: {
      type: Number,
      required: false
    },
    progress: {
      type: Number,
      required: false
    }
  }
}
</script>
<style lang="scss" scoped>
.media-player {
  &__progress-bar {
    position: absolute;
    top: -1.75em;
    left: .5em;
    width: calc(100% - 1em);
    height: 2em;
    cursor: pointer;
    user-select: none;
    &__inner {
      position: relative;
      top: 1em;
    }


    &__value-container {
      width: 100%;
      height: .25em;
      background: var(--channel-colors-inside-buttons);
    }

    &__value {
      background: var(--channel-colors-inside-buttons-hover);
      height: 100%;
    }

    &__handle-container {
      position: absolute;
      top: -.35em;
      width: 100%;
      &__inner {
        position: relative;
        text-align: center;
        top: -1.75em;
      }
    }

    &__handle {
      position: relative;
      width: 1em;
      height: 1em;
      border-radius: 50%;
      background: var(--channel-colors-inside-buttons);
    }

    &__thumbnail {
      border-radius: .25em;
      border: 1px solid var(--channel-colors-inside-buttons);
    }

    &__time-container {
      position: absolute;
      z-index: 10000;
      top: -1.25em;
    }

    &__time {
      position: relative;
      top: -.75em;
      transition: all .2s;
      opacity: 0;
      transform: scale(.75);
      transform-origin: center 2em;
      text-align: center;
      &__text {
        font-weight: 600;
      }
    }

    &__inner:hover &__time {
      opacity: 1;
      transform: scale(1);
    }
  }
}
</style>
