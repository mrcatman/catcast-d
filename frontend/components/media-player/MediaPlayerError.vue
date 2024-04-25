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
    top: calc(50% - 1em);
    height: 2em;
    width: 100%;
    background: var(--channel-colors-inside-panels);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: .75;
    font-size: 1.25em;
    font-weight: 500;
    &__text {
      margin: 0 0 0 .5em;
    }
  }
}
</style>
<script>
export default {
  computed: {
    errorText() {
      console.log('error', this.error);
      if (this.error.code === 4) { // todo: other codes
        return this.$t('player._errors.wrong_format_or_not_found');
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
