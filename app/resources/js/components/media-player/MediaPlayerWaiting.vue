<template>
  <div class="media-player__waiting">
    <div class="media-player__waiting__inner">
      <c-preloader  />
      <span class="media-player__waiting__text">{{$t('player.broadcast_will_start_soon')}}</span>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.media-player {
  &__waiting {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    &__inner {
      background: var(--channel-colors-inside-panels);
      opacity: .75;
      display: flex;
      align-items: center;
      padding: 1em;
    }

    &__text {
      font-size: 1.25em;
      margin: 0 0 0 .5em;
      font-weight: 500;
    }
  }
}
</style>
