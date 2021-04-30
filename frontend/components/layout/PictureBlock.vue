<template>
  <div class="picture-block" :class="{'picture-block--default': !this.data || !this.data.full_url}" :style="{backgroundImage: `url(${picture}`}"></div>
</template>
<style lang="scss">
.picture-block {
  &--default {
    background-size: 80% !important;
  }
}
</style>
<script>
export default {
  name: 'PictureBlock',
  computed: {
    picture() {
      if (this.data) {
        return this.data.full_url;
      }
      if (this.defaultFromConfig) {
        const config = this.$accessor.modules.site?.config;
        return config[this.defaultFromConfig];
      }
      return '';
    }
  },
  props: {
    data: {
      type: Object,
      required: false
    },
    defaultFromConfig: {
      type: String,
      required: false
    }
  }
}
</script>
