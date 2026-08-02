<template>
  <div class="media-player__error">
    <c-icon class="media-player__error__icon" icon="fa-exclamation-triangle" />
    <span class="media-player__error__text">
      {{errorText}}
    </span>
  </div>
</template>
<style lang="scss" scoped>
.media-player {
  &__error {
    position: absolute;
    top: calc(50% - 2em);
    height: 4em;
    width: 100%;
    background: var(--channel-colors-inside-panels);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: .75;
    font-size: 1.25em;
    font-weight: 500;
    &__text {
      margin-left: .5em;
    }
  }
}
</style>
<script>
export default {
  computed: {
    errorText() {
      if (this.error.code === 2 || this.error.code === 4) { // todo: other codes
        return this.$t('player.errors.wrong_format_or_not_found');
      }
      return '';
    },
  },
  props: {
    error: {
      type: Object,
      required: false
    }
  }
}
</script>
