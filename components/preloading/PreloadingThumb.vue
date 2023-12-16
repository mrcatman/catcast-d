<template>
  <common-thumb class="preloading-thumb">
    <template slot="texts">
      <div class="preloading-thumb__text">
        <preloading-line />
        <preloading-line />
        <preloading-line />
        <preloading-line />
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
    }
  }

</style>
<script>
  import CommonThumb from "@/components/thumbs/CommonThumb";
  import PreloadingLine from "@/components/preloading/PreloadingLine";
  export default {
    components: {
      CommonThumb,
      PreloadingLine
    }
  }
</script>
