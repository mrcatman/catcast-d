<template>
  <div class="slider" ref="slider" @mousedown="onSliderMouseDown">
    <div class="slider__inner">
      <div class="slider__bar">
        <div class="slider__bar__percent" :style="{width: (val * 100) + '%'}"></div>
      </div>
      <div class="slider__handle" :style="{left: (val * 100) + '%'}"></div>
    </div>
  </div>
</template>
<style lang="scss">
  .slider {
    height: 1.25em;
    display: flex;
    align-items: center;
    cursor: pointer;
    overflow: hidden;
    &__inner {
      width: 100%;
      position: relative;
    }

    &__handle {
      position: absolute;
      width: .75em;
      height: .75em;
      background: var(--active-color);
      top: .25em;
      border-radius: 50%;
    }

    &__bar {
      width: 100%;
      height: 3px;
      background: rgba(255, 255, 255, .25);
      margin: .5em 0;
      border-radius: 1em;
      overflow: hidden;
      &__percent {
        height: 100%;
        background: var(--active-color);
      }
    }
  }
</style>
<script>
  export default {
    props: {
      value: {
         required: true,
      }
    },
    watch: {
      value(newVal) {
        this.val = newVal;
      }
    },
    data() {
      return {
        val: this.value < 1 ? this.value : 1
      }
    },
    methods: {
      setValue(val) {
        this.val = val;
      },
      onSliderMouseDown(e) {
        let onMouseMove = (e) => {
          let slider = this.$refs.slider;
          let rect = slider.getBoundingClientRect();
          let width = rect.width;
          let left = rect.left;
          let x = e.clientX;
          let percent = (x - left) / width;
          if (percent > 1) {
            percent = 1;
          }
          if (percent < 0) {
            percent = 0;
          }
          this.val = percent;
          this.$emit('input', percent)
          this.$emit('change', percent)
        };
        onMouseMove(e);
        window.addEventListener('mousemove', onMouseMove);
        window.addEventListener('mouseup', () => {
          window.removeEventListener('mousemove', onMouseMove);
        });
        if(e.stopPropagation) e.stopPropagation();
        if(e.preventDefault) e.preventDefault();
        e.cancelBubble = true;
        e.returnValue = false;
        return false;
      },
    }
  }
</script>
