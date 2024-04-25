<template>
  <span class="tooltip-icon">
    <i :class="icon"></i>
    <c-tooltip :position="position">
      <slot></slot>
    </c-tooltip>
  </span>
</template>
<style lang="scss">
.tooltip-icon {
  margin-left: .25em;
}
</style>
<script>
export default {
  props: {
    icon: {
      type: String,
      default: 'fa fa-info-circle'
    },
    position: String,
  }
}
</script>
