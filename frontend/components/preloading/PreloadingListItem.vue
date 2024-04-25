<template>
  <c-list-item class="preloading-list-item" :picture="siteLogoSquare" :picture-square="true">
    <template slot="captions">
      <preloading-line />
      <preloading-line />
      <preloading-line />
    </template>
  </c-list-item>
</template>
<style lang="scss" scoped>
  .preloading-list-item {
    ::v-deep .list-item__picture {
      opacity: .5;
    }
  }
</style>
<script>
  import PreloadingLine from "@/components/preloading/PreloadingLine";
  import {mapGetters} from "vuex";
  export default {
    computed: {
      ...mapGetters('config', ['siteLogoSquare']),
    },
    components: {
      PreloadingLine
    },
    props: {
      config: {
        type: Object,
        required: false,
        default() {
          return {}
        }
      }
    }
  }
</script>
