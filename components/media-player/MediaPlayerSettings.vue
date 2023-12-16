<template>
  <div class="media-player__settings">
    <media-player-button icon="settings" @click="menuVisible = true" :tooltip="$t('player.settings._title')" />
    <c-popup-menu manual position="top-left" v-model="menuVisible">
      <c-popup-menu-item>
        <div class="media-player__setting">
          <span class="media-player__setting__name">{{$t('player.settings.quality')}}&nbsp;</span>
          <span class="media-player__setting__value">{{settings.source && settings.source.quality_name ? settings.source.quality_name : $t('player.default_quality')}}</span>
        </div>
        <c-popup-menu position="top-left">
          <c-popup-menu-item @click="settings.source = file" v-for="file in media.files" :key="file.id">
            <span class="media-player__setting__available-value" :class="{'media-player__setting__available-value--active': settings.source && settings.source.id === file.id}">{{file.quality_name || $t('player.default_quality')}}</span>
          </c-popup-menu-item>
        </c-popup-menu>
      </c-popup-menu-item>
      <c-popup-menu-item>
        <div class="media-player__setting">
          <span class="media-player__setting__name">{{$t('player.settings.playback_rate')}}&nbsp;</span>
          <span class="media-player__setting__value">{{settings.playbackRate || 1}}x</span>
        </div>

        <c-popup-menu position="top-left">
          <c-popup-menu-item @click="settings.playbackRate = playbackRate" v-for="playbackRate in playbackRates" :key="playbackRate">
            <span class="media-player__setting__available-value" :class="{'media-player__setting__available-value--active': settings.playbackRate === playbackRate}">{{playbackRate}}</span>
          </c-popup-menu-item>
        </c-popup-menu>
      </c-popup-menu-item>
    </c-popup-menu>
  </div>

</template>
<script>
import MediaPlayerButton from "@/components/media-player/MediaPlayerButton";

const playbackRates = [.25, .5, .75, 1, 1.25, 1.5, 1.75, 2];
export default {
  data() {
    return {
      settings: this.value,
      menuVisible: false,
      playbackRates
    }
  },
  components: {
    MediaPlayerButton
  },
  watch: {
    value(settings) {
      this.settings = settings;
    },
    settings(settings) {
      this.$emit('input', settings);
    },
  },
  props: {
    media: {
      type: Object,
      required: true
    },
    value: {
      type: Object,
      required: true
    }
  }
}
</script>
<style lang="scss" scoped>
.media-player {
  &__settings {
    position: relative;
    ::v-deep .popup-menu {
      visibility: hidden;
      opacity: 0;
      transition: opacity .4s;

    }
    .media-player--hovered & ::v-deep .popup-menu {
      visibility: visible;
      opacity: 1;
    }
  }

  &__setting {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    font-size: .9375em;
    font-weight: 300;

    &__value {
      margin-left: .5em;
      font-size: 1.125em;
      font-weight: bold;
    }

    &__available-value {
      min-width: 3.5em;
      text-align: right;

      &--active {
        font-weight: bold;
      }
    }
  }
}
</style>
