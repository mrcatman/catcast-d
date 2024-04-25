<template>
  <a @click="onClick" class="media-player__button">
    <c-tooltip v-if="tooltip" :position="tooltipPosition">{{tooltip}}</c-tooltip>
    <c-icon :icon="icon"  />
  </a>
</template>
<style lang="scss">
.media-player {
  &__button {
    cursor: pointer;
    color: var(--channel-colors-inside-buttons) !important;
    transition: color .35s;
    font-size: 1.25em;
    padding: .5em;
    display: flex;
    align-items: center;
    justify-content: center;
    &:hover {
      color: var(--channel-colors-inside-buttons-hover) !important;
    }
  }
}
</style>
<script>
export default {
  methods: {
    onClick(e) {
      this.$emit('click', e);
    }
  },
  props: {
    tooltip: String,
    tooltipPosition: String,
    icon: {
      type: String,
      required: true
    }
  }
}
</script>
