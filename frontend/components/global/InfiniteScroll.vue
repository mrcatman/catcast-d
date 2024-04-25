<template>
  <div ref="list" class="infinite-scroll">
    <div class="infinite-scroll__top" ref="top"></div>
    <slot></slot>
    <div :style="preloaderStyle"  v-if="loading" class="infinite-scroll__loading">
      <c-preloader />
    </div>
  </div>
</template>
<style lang="scss">
  .infinite-scroll {
    position: relative;
    &__loading {
      margin-top: -4em;
      opacity: .75;
      height: 3em;
      position: fixed;
      bottom: 0;
      width: 100%;
      text-align: center;
      padding: 1em 0;
      background: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.5));
    }
  }
</style>
<script>
const threshold = 36;
  export default {
    computed: {
      preloaderStyle() {
        return {
          'width': this.preloaderPosition.width + 'px',
          'left': this.preloaderPosition.x + 'px',
          'top': this.preloaderPosition.y + 'px'
        }
      }
    },
    methods: {
      onScroll() {
        if (!this.loading) {
          const listEl = this.$refs.list;
          if (listEl) {
            const rect = listEl.getBoundingClientRect();
            if (Math.abs(rect.bottom - window.innerHeight) < threshold) {
              this.$emit('scroll');
            }
            if (this.$listeners.scrollToTop) {
              const topEl = this.$refs.top;
              const topRect = topEl.getBoundingClientRect();
              if (Math.abs(topRect.top - rect.top) < threshold) {
                this.$emit('scrollToTop');
              }
            }
          }
        }
      },
      setPreloaderPosition() {
        let list = this.$refs.list;
        let rect = list.getBoundingClientRect();
        let top = rect.top + rect.height;

        if (top > window.innerHeight) {
          top = window.innerHeight;
        }
        this.preloaderPosition = {
          width: rect.width,
          x: rect.left,
          y: top
        }
      }
    },
    data() {
      return {
        lastScrollTop: 0,
        preloaderPosition: {
          width: 0,
          x: 0,
          y: 0,
        }
      }
    },
    watch: {
      loading(loading) {
        if (loading) {
          this.setPreloaderPosition();
        }
      }
    },
    props: {
      loading: Boolean,
    },
    mounted() {
      document.addEventListener('mousewheel', this.onScroll);
      document.addEventListener('touchend', this.onScroll);
    },
    beforeDestroy() {
      document.removeEventListener('mousewheel', this.onScroll);
      document.removeEventListener('touchend', this.onScroll);
    },
  }
</script>
