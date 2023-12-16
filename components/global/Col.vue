<template>
  <div class="col" :class="classes" :style="{flexGrow: grow !== undefined ? grow : null}">
    <slot></slot>
  </div>
</template>
<script>
export default {
  computed: {
    classes() {
      return {
        'col--auto': this.autoWidth,
        'col--with-button': this.withButton,
        'col--without-titles': this.withoutTitles,
        'col--mobile-full-width': this.mobileFullWidth
      }
    }
  },
  props: {
    autoWidth: Boolean,
    withButton: Boolean,
    withoutTitles: Boolean,
    limitWidth: Boolean,
    mobileFullWidth: Boolean,
    grow: Number,
  }
}
</script>
<style lang="scss" scoped>
.col {
  flex: 1;
  margin: 0 .5em;

  &--auto {
    flex: unset;
  }

  &--mobile-full-width {
    @media screen and (max-width: 768px) {
      width: 100%;
      flex: unset!important;
    }
  }

  &--with-button {
    flex: 0;
    display: flex;
    align-items: center;
    padding-top: 2em;
    @media screen and (max-width: 768px) {
      margin-top: .5em;
      margin-bottom: .5em;
    }
  }

  &--without-titles {
    padding-top: 0;
  }
  &--no-margin {
    margin: 0 !important;
  }
}
</style>
