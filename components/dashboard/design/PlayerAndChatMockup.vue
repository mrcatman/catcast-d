<template>
  <div class="page-mockup">
    <div class="page-mockup__player">
      <div class="page-mockup__player__inner" :style="{backgroundImage: 'url(' + (picturesData.player_background ? picturesData.player_background.full_url : '') + ')'}">
        <div class="page-mockup__player__play-icon">
          <i class="material-icons">play_circle_outline</i>
        </div>
      </div>
    </div>
    <div class="page-mockup__chat">
      <div class="page-mockup__chat__block">
        <div class="page-mockup__chat__texts">
          <span></span>
          <span></span>
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
      <div class="page-mockup__chat__panel" >
        <div class="page-mockup__chat__input">...</div>
        <div class="page-mockup__chat__button">...</div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    channel: Object,
    picturesData: Object,
  }
}
</script>
