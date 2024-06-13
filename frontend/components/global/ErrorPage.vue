<template>
<div class="error-page">
  <div class="error-page__texts">
    <div class="error-page__header">{{data.message ? $t(data.message) : data.title}}</div>
    <div class="error-page__text" v-html="data.text || $t('errors.write_admin')"></div>
    <c-button v-if="!inIframe" flat to="/">{{$t('global.back_to_main_page')}}</c-button>
    <a target="_blank" href="/" class="error-page__logo-container" v-else>
      <img :src="siteLogo" class="error-page__logo" />
    </a>
  </div>
  <svg class="error-page__testcard"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 24">
    <path d="m0,0h8v24H0"/>
    <path fill="#0000C0" d="m0,0h7v24H0"/>
    <path fill="#C00000" d="m0,0h6v24H0"/>
    <path fill="#C000C0" d="m0,0h5v24H0"/>
    <path fill="#00C000" d="m0,0h4v24H0"/>
    <path fill="#00C0C0" d="m0,0h3v24H0"/>
    <path fill="#C0C000" d="m0,0h2v24H0"/>
    <path fill="#FFFFFF" d="m0,0h1v24H0"/>
  </svg>
</div>
</template>
<style lang="scss">
  .error-page {
    width: 100%;
    height: 100%;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    flex: 1;
    color: #fff;
    &__logo {
      margin-top: 1em;
      height: 3em;
    }

    @media screen and (max-width: 768px) {
      height: calc(100vh - 7em);
    }
    .player-page & {
      height: 100%!important;
    }
    &__testcard {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
    }
    &__texts {
      position: relative;
      z-index: 1;
      width: 100%;
      padding: 3em;
      background: #000;
    }
    &--not-found &__texts {
      background: rgba(0, 0, 0, 0.75);
    }

    &__header {
      font-size: 2em;
      font-weight: 500;
      margin: 0 0 .25em;
    }
    &__text {
      font-size: 1.25em;
      margin: 0 0 .5em;
    }

    &__picture {
      width: 100%;
    }
  }
</style>
<script>
import {mapGetters} from "vuex";

export default {
  computed: {
    ...mapGetters('config', ['siteLogo']),
    inIframe() {
      try {
        return window.self !== window.top;
      } catch (e) {
        return true;
      }
    },

  },

	props: {
		data: {
			type: Object,
			required: true,
		}
	}
}
</script>
