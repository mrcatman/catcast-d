<template>
  <div @mousedown="onMouseDown" ref="volume_bar" class="media-player__volume-bar">
    <div class="media-player__volume-bar__handle" :style="{left: volume + '%'}"></div>
    <div class="media-player__volume-bar__value-container">
      <div class="media-player__volume-bar__value" :style="{width: volume + '%'}">
        <c-tooltip position="top-left">{{$t('player.volume')}}</c-tooltip>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    value: {
      type: Number,
      required: true
    }
  },
  watch: {
    value(newVolume) {
      this.volume = newVolume;
    },
    volume(newVolume) {
      this.$emit('input', newVolume);
    }
  },
  data() {
    return {
      volume: this.value
    }
  },
  methods: {
    onMouseDown(e) {
      let bar = this.$refs.volume_bar;
      let rect = bar.getBoundingClientRect();
      let width = rect.width;
      let left = rect.left;
      let onMouseMove = (e) => {
        let x = e.clientX;
        let percent = (x - left) / width * 100;
        if (percent > 100) {
          percent = 100;
        }
        if (percent < 0) {
          percent = 0;
        }
        this.volume = percent;
      };
      onMouseMove(e);
      window.addEventListener('mousemove', onMouseMove);
      window.addEventListener('mouseup', () => {
        window.removeEventListener('mousemove', onMouseMove);
      })
    },
  }
}
</script>
<style lang="scss" scoped>
.media-player {
  &__volume-bar {
    height: 1em;
    position: relative;
    top: .125em;
    width: 5em;
    transition: all .4s;
    cursor: pointer;

    &__value-container {
      height: .25em;
      margin-top: .25em;
      background: var(--channel-colors-inside-panels);
    }

    &__handle {
      width: .75em;
      height: .75em;
      background: var(--channel-colors-inside-buttons);
      border-radius: 50%;
      position: absolute;
      top: 0;
    }

    &__handle-container {
      top: -.35em;
      width: 100%;
      background: var(--channel-colors-inside-buttons);
      height: .25em;
      margin: .25em 0 0;
    }

    &__value {
      height: 100%;
      background: var(--channel-colors-inside-buttons);
    }
  }
}
</style>
