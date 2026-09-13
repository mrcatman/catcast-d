<template>
  <div class="input__title" :class="classes">
    <slot></slot>
  </div>
</template>
<script>
export default {
  computed: {
    classes() {
      return {
        'input__title--with-icon': this.withIcon
      }
    }
  },
  props: {
    withIcon: Boolean,
  }
}
</script>
<style lang="scss" scoped>
.input__title {
  font-size: 1em;
  white-space: nowrap;
  line-height: 1.9375;
  color: var(--input-titles-color);
  &--with-icon {
    left: 2.5em;
  }
}
</style>
