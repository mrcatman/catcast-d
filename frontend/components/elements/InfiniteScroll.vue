<template>
  <div ref="list" class="infinite-scroll">
    <slot ref="list"></slot>
    <div :style="{'width': preloaderPosition.width+'px', 'left': preloaderPosition.x+'px', 'top': preloaderPosition.y+'px'}"  v-if="loading" class="infinite-scroll__loading">
      <m-preloader stroke-width="3" circle-width="2em"  />
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
  export default {
    methods: {
      setPreloaderPosition() {
        let list = this.$refs.list;
        let rect = list.getBoundingClientRect();
        let scrollTop = list.scrollTop;
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
      loading(isLoading) {
        if (isLoading) {
          this.setPreloaderPosition();
        }
      }
    },
    props :{
      pageScroll: {
        type: [Boolean],
        required: false,
      },
      loading: {
        type: [Boolean],
        required: true
      }
    },
    mounted() {
      this.$nextTick(() => {
        let list = this.$refs.list;
        if (list) {
          if (this.pageScroll) {
           // window.addEventListener('scroll', (e) => {
           //   let rect = list.getBoundingClientRect();
           //   console.log(rect);
           // })
          } else {
            let em = parseFloat(getComputedStyle(list).fontSize);
            list.addEventListener('scroll', (e) => {
              if (!this.loading) {
                let isScrollDown = list.scrollTop - this.lastScrollTop > 0;
                if ((list.clientHeight + list.scrollTop + em) >= list.scrollHeight && isScrollDown) {
                  this.$emit('scroll');
                }
                if (list.scrollTop < em && !isScrollDown) {
                  this.$emit('scrollTop');
                }
                this.lastScrollTop = list.scrollTop;
              }
            })
          }
        }
      })
    }
  }
</script>
