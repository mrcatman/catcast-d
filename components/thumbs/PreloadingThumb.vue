<template>
  <common-thumb class="preloading-thumb">
    <template slot="texts">
      <div class="preloading-thumb__text">
        <div class="preloading-thumb__text__line preloading-thumb__text__line--first" ></div>
        <div class="preloading-thumb__text__line preloading-thumb__text__line--second" ></div>
      </div>
    </template>
  </common-thumb>
</template>
<style lang="scss">
  .preloading-thumb {
    animation: preloadingBlock 2.5s infinite;
    &__text {
      padding: .5em .5em .25em;
      border-bottom-left-radius: .25em;
      border-bottom-right-radius: .25em;
      background: var(--box-footer-color);
      font-weight: 600;
      &__line {
        background: rgba(255, 255, 255, 0.52);
        height: .5em;
        width: calc(100% - 1em);
        margin: .5em;
        border-radius: .125em;
        &--second {
          opacity: .75;
          width: calc(100% - 5em);
        }
      }
    }
  }
  @keyframes preloadingBlock {
    0% {
      opacity: .5;
    }
    50% {
      opacity: .75;
    }
    100% {
      opacity: .5;
    }
  }
</style>
<script>
  import CommonThumb from "./CommonThumb";
  export default {
    components: {CommonThumb},
    data() {
      return {
        textWidth: 100,// 25 + Math.random() * 75
      }
    }
  }
</script>
