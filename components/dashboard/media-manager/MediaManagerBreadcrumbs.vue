<template>
  <div class="media-manager__breadcrumbs" >
    <a class="media-manager__breadcrumb" @click="goBreadcrumb()">{{$t('dashboard.media._title')}} </a>
    <a class="media-manager__breadcrumb" :class="{'media-manager__breadcrumb--active': $index === breadcrumbs.length - 1}" :key="$index" v-for="(item, $index) in breadcrumbs" @click="goBreadcrumb(item, $index)">/ {{item.title}}</a>
  </div>
</template>
<script>
export default {
  methods: {
    goToTop() {
      this.$emit('folderClick', null);
    },
    goBreadcrumb(item, index) {
      this.$emit('folderClick', item, index);
    }
  },
  props: {
    breadcrumbs: {
      type: Array,
      required: true
    }
  }
}
</script>
<style lang="scss">
.media-manager {
  &__breadcrumb {
    cursor: pointer;
    opacity: .75;
    &:hover {
      opacity: 1;
    }
    &--active {
      opacity: .85;
    }
  }
}
</style>
