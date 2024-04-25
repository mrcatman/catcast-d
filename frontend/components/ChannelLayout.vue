<template>
  <custom-colors class="channel-layout" ref="page" :colors-scheme="currentColorsScheme" :style="pageStyle">
    <div class="channel-layout__inner">
      <div class="channel-layout__content" ref="content">
        <nuxt-link target="_blank" :to="bannerLink" v-if="banner">
          <img class="channel-layout__banner" :src="banner"/>
        </nuxt-link>
        <slot name="main"></slot>
      </div>
    </div>
    <div class="channel-layout__sidebar">
      <slot name="sidebar"></slot>
    </div>
  </custom-colors>
</template>
<script>
import CustomColors from "@/components/CustomColors";

export default {
  components: {CustomColors},
  computed: {
    banner() {
      return this.playlist ? this.playlist.banner : this.channel.banner;
    },
    bannerLink() {
      return this.playlist ?`/playlist/${this.playlist.id}` : `/${this.channel.shortname}`;
    },
    currentColorsScheme() {
      return this.design?.colors_scheme || {};
    },
    design() {
      return this.playlist ? this.playlist : this.channel;
    },
    pageStyle() {
      if (this.design?.background) {
        return {
          backgroundImage: `url(${this.design?.background})`
        }
      }
      return {};
    },
  },
  props: {
    channel: {
      type: Object,
      required: true
    },
    playlist: {
      type: Object,
      required: false
    },
  }
}
</script>
<style lang="scss">
.channel-layout {
  padding-top: 1em;
  background: var(--channel-colors-page-background);

  background-attachment: fixed;
  background-position: center;
  background-size: cover;
  &__banner {
    max-width: 100%;
    margin-bottom: 1em;
    width: 100%;
    max-height: 12em;
    object-fit: cover;
  }

  &__inner {
    width: calc(100% - 35em);
    display: flex;
    align-items: center;
    position: relative;
    z-index: 1;
    color: var(--channel-colors-page-texts);
  }
  a:not(.button) {
    color: var(--channel-colors-page-links);
  }
  &__content {
    overflow: auto;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
    min-height: 100%;
    width: 100%;
    max-width: 1480px;
    margin: 0 auto;
    padding: 0 1em;
  }


  &__player-container{
    position: relative;
    transition: all .1s;
    &--video {
      padding-top: 60%;
      .media-player {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
      }
    }
  }
  &__sidebar {
    display: flex;
    position: fixed;
    top: 3.5em;
    right: 0;
    width: 35em;
    height: calc(100vh - 3.5em);
  }

  &__title-container {
    margin: .5em 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  &__title {
    font-size: 1.125em;
    font-weight: 600;
    margin-right: 1em;
    color: var(--channel-colors-page-headings);
  }

  .preloader__outer:before {
    background: var(--box-color);
    opacity: .5;
  }

  .comments {
    margin: 1em 0;
  }

  &__records {
    width: 100%;
  }

  .video-item__inner:hover, .project-item__inner:hover {
    box-shadow: 0 .25em .25em var(--channel-colors-page-buttons-texts);
  }
}
.channel-layout .video-item--view-list .video-item__inner:hover {
  box-shadow: 0 0.25em 0.75em -0.25em var(--channel-colors-page-buttons-texts);
}
@media screen and (max-width: 768px) {
  .channel-layout {
    flex: 1;
    height: 100%;
    padding: 0;
    width: 100vw;
    overflow: visible;
    &__content {
      padding: 0;
    }
    &__box {
      &--tabs {
        font-size: .875em;
      }
    }
    &__bottom {
      &__tab {
        &--chat {
          height: unset;
          flex: 1;
        }
      }
      &__inner {
        padding: 0;
      }
    }

    &__inner {
      width: 100vw;
      flex-direction: column;
    }

    .buttons-row {
      flex-direction: column;
    }


    &__chat-container {
      max-width: 100%;
    }
    &__outer {
      flex: 1;
    }
    &__player-container {
      position: relative;
      height: auto!important;
      padding-top: 60%;
    }
    &__player {
      position: absolute!important;
      top: 0;
      left: 0;
    }
  }
  .videos-section {
    height: 100%;
  }
  .videos-section__header {
    background: none;
    /* background: var(--channel-colors-page-panels); */
  }
  .records-section__header {
    background: var(--channel-colors-page-panels);
  }


}

</style>
