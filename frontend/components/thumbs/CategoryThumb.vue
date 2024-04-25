<template>
  <common-thumb class="category-thumb" :link="{path: link, params: {channel: data}}" :picture="picture" >
    <template slot="inside_picture">
      <div class="category-thumb__name">{{data.name}}</div>
    </template>
  </common-thumb>
</template>
<style lang="scss">
  .category-thumb {
    &__name {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 100%;
      background: var(--darken-4);
      font-size: 1.625em;
      font-weight: bold;
    }
  }
</style>
<script>

  import CommonThumb from "./CommonThumb";

  export default {
    components: {CommonThumb},
    computed: {
      //...mapGetters('config', ['siteLogoSquare']),
      link() {
        return `/directory/category/${this.data.id}`;
      },
      picture() {
        return this.data.picture; // todo: pictures for categories
      },
    },
    props: {
      data: {
        type: Object,
        required: true
      },
    }
  }
</script>
