<template>
  <div class="media-player__logo-container" ref="container">
    <div class="media-player__logo-container__inner"  v-if="logo" :style="{height: realHeight + 'px'}">
      <img :src="logo.picture.full_url" class="media-player__logo" :style="{width: (logo.position.width * 100) + '%', left: (logo.position.x * 100) + '%', top: (logo.position.y * 100) + '%' }" />
    </div>
  </div>
</template>
<script>
export default {
  mounted() {
    this.setHeight();
    window.addEventListener('resize', this.setHeight);
  },
  beforeDestroy(){
    window.removeEventListener('resize', this.setHeight);
  },
  watch: {
    videoDimensions() {
      this.setHeight();
    }
  },
  methods: {
    setHeight() {
      this.realHeight = this.$refs.container.offsetWidth * (this.videoDimensions.height / this.videoDimensions.width);
    }
  },
  data() {
    return {
      realHeight: 0
    }
  },
  props: {
    logo: {
      type: Object,
      required: false
    },
    videoDimensions: {
      type: Object,
      required: true
    }
  }
}
</script>
<style lang="scss" scoped>
.media-player {
  &__logo-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    &__inner {
      width: 100%;
      height: 100%;
      position: relative;
    }
  }
  &__logo {
    position: absolute;
  }
}
</style>
