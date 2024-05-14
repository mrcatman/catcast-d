<template>
  <div class="dashboard-page__design" ref="design_preview">
    <div class="dashboard-page__design__section">
      <c-box  class="dashboard-page__design__section__inputs">
        <template slot="title">{{$t('dashboard.design.page.heading')}}</template>
        <template slot="main">
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.page.logo')}}</div>
            <c-picture-uploader big :channel="channel" folder="logos" v-model="picturesData.logo" :errors="errors.logo" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.page.banner')}}</div>
            <c-picture-uploader big :proportion="5" :channel="channel"  folder="banners" v-model="picturesData.banner" :errors="errors.banner" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.page.background')}}</div>
            <c-picture-uploader big :proportion="16/9" :channel="channel"  folder="backgrounds" v-model="picturesData.background" :errors="errors.background" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.page.colors.texts')}}</div>
            <c-colorpicker v-model="colorsScheme.page_texts" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.page.colors.links')}}</div>
            <c-colorpicker v-model="colorsScheme.page_links" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.page.colors.blocks_background')}}</div>
            <c-colorpicker v-model="colorsScheme.page_panels" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.page.colors.headers')}}</div>
            <c-colorpicker v-model="colorsScheme.page_headings" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.page.colors.background')}}</div>
            <c-colorpicker v-model="colorsScheme.page_background" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.page.colors.buttons')}}</div>
            <c-colorpicker v-model="colorsScheme.page_buttons" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.page.colors.buttons_texts')}}</div>
            <c-colorpicker v-model="colorsScheme.page_buttons_texts" />
          </div>
        </template>
      </c-box>
      <div class="dashboard-page__design__mockup-container">
        <channel-page-mockup :channel="data" :picturesData="picturesData" />
      </div>
    </div>

    <div class="dashboard-page__design__section">
      <c-box class="dashboard-page__design__section__inputs">
        <template slot="title">{{$t('dashboard.design.player_and_chat.heading')}}</template>
        <template slot="main">
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.player_and_chat.player_background')}}</div>
            <c-picture-uploader big :proportion="16/9" :channel="channel" folder="player_backgrounds" v-model="picturesData.player_background" :errors="errors.player_background" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.player_and_chat.colors.panels')}}</div>
            <c-colorpicker v-model="colorsScheme.inside_panels" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.player_and_chat.colors.texts')}}</div>
            <c-colorpicker v-model="colorsScheme.inside_texts" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.player_and_chat.colors.buttons')}}</div>
            <c-colorpicker v-model="colorsScheme.inside_buttons" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.player_and_chat.colors.buttons_hover')}}</div>
            <c-colorpicker v-model="colorsScheme.inside_buttons_hover" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.player_and_chat.colors.buttons_texts')}}</div>
            <c-colorpicker v-model="colorsScheme.inside_buttons_texts" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.player_and_chat.colors.background')}}</div>
            <c-colorpicker v-model="colorsScheme.inside_background" />
          </div>
          <div class="dashboard-page__design__section__row">
            <div class="dashboard-page__design__section__title">{{$t('dashboard.design.player_and_chat.colors.inputs')}}</div>
            <c-colorpicker v-model="colorsScheme.inside_inputs_texts" />
          </div>
        </template>
      </c-box>
      <div class="dashboard-page__design__mockup-container">
        <player-and-chat-mockup :channel="data" :picturesData="picturesData" />
      </div>
    </div>
  </div>
</template>
<style lang="scss">
.dashboard-page__design {
  &__section {
    margin: 0 0 1em;
    display: flex;
    align-items: flex-start;
    @media screen and (max-width: 768px) {
      flex-direction: column;
    }
    &__title {
      width: 100%;
    }
    &__row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    &__inputs {
      flex: 3;
      margin: 0 1em 0 0;
      @media screen and (max-width: 768px) {
        width: 100%;
        margin: 0 0 1em;
      }
    }


  }
  &__mockup-container {
    position: sticky;
    top: 0;
    flex: 1.5;
    @media screen and (max-width: 768px) {
      position: relative;
      width: 100%;
    }
  }
}
.page-mockup {
  &__main {
    background-color: var(--edit-channel-colors-page-headings);
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
    padding: 1em;
    &__box {
      padding: 1em;
      color: var(--edit-channel-colors-page-texts);
      background: var(--edit-channel-colors-page-panels);
    }

    &__header {
      display: flex;
      align-items: center;
    }

    &__channel-banner {
      background-repeat: no-repeat;
      background-position: center;
      background-size: contain;
      height: 3em;
      margin: .5em 0;
    }

    &__channel-logo {
      background-repeat: no-repeat;
      background-position: center;
      background-size: contain;
      margin: 0 .5em 0 0;
      width: 3em;
      height: 3em;
    }

    &__channel-name {
      font-weight: 500;
      font-size: 1.25em;
      color: var(--edit-channel-colors-page-headings);
    }

    &__channel-description {
      margin: .5em 0;
    }

    &__bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    &__link {
      font-size: .875em;
      color: var(--edit-channel-colors-page-links) !important;
    }

    &__button {
      background: var(--edit-channel-colors-page-buttons);
      color: var(--edit-channel-colors-page-buttons-texts);
    }
  }

  &__player {
    background: var(--edit-channel-colors-inside-background);

    &__inner {
      background-repeat: no-repeat;
      background-position: center;
      background-size: contain;
      display: flex;
      padding: 5em;
      align-items: center;
      justify-content: center;
    }

    &__play-icon i {
      font-size: 5em;
    }
  }

  &__chat {
    &__block {
      background: var(--edit-channel-colors-inside-panels);
    }

    &__texts {
      padding: 1em;

      & span {
        background: var(--edit-channel-colors-inside-texts);
        display: block;
        width: 100%;
        height: 5px;
        margin: 1em 0 0;
      }
    }

    &__panel {
      background: var(--edit-channel-colors-inside-panels);
      display: flex;
      justify-content: space-between;
      padding: 1em;
    }

    &__input {
      border-bottom: 1px solid var(--edit-channel-colors-inside-inputs-texts);
      color: var(--edit-channel-colors-inside-inputs-texts);
      flex: 5;
    }

    &__button {
      cursor: pointer;
      transition: all .4s;
      flex: 1;
      text-align: center;
      background: var(--edit-channel-colors-inside-buttons);
      color: var(--edit-channel-colors-inside-buttons-texts);
    }
  }
}
</style>
<script>
import ChannelPageMockup from "./ChannelPageMockup";
import PlayerAndChatMockup from "./PlayerAndChatMockup";

export default {
  components: {PlayerAndChatMockup, ChannelPageMockup},
  props: {
    channel: {
      type: Object,
      required: true
    },
    showSaveButton: {
      type: Boolean,
      required: false,
    },
    data: {
      type: Object,
      required: true
    }
  },
  watch: {
    picturesData: {
      handler() {
        this.update();
      },
      deep: true
    },
    colorsScheme: {
      handler() {
        this.updateColorsVariables();
        this.update();
      },
      deep: true
    }
  },

  data() {
    return {
      loading: false,
      colorsScheme: this.data.colors_scheme || {},
      picturesData: this.data.pictures_data || {},
      errors: {},
    }
  },
  mounted() {
    this.updateColorsVariables();
  },
  methods: {
    update() {
      this.$emit('input', {
        colors_scheme: this.colorsScheme,
        pictures_data: this.picturesData,
      })
    },
    updateColorsVariables() {
      for (let key in this.colorsScheme) {
        this.$refs.design_preview.style.setProperty(`--edit-channel-colors-${key.replace(/_/g, '-')}`, this.colorsScheme[key]);
      }
    }
  }
}
</script>
