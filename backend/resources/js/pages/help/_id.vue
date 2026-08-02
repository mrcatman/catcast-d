<template>
  <div class="help-page__outer">
    <div v-if="!error"  class="help-page">
      <div class="help-page__title">{{page.title}}</div>
      <div class="help-page__contents" v-html="page.contents"></div>
    </div>
    <c-error-page v-else :data="error"/>
  </div>
</template>
<style lang="scss">
  .help-page {
    height: 100%;
    display: flex;
    flex-direction: column;
    &__outer {
      height: 100%;
    }
    p {
      margin: 0;
    }
    p br {
      display: none;
    }
    &__title {
      font-size: 1.75em;
      font-weight: 600;
      padding: .5em;
      background: var(--box-header-color);
      color: var(--active-color);
    }

    &__contents {
      padding: 1em;
      overflow: auto;
      overflow-x: hidden;
      font-size: 1.125em;
      word-wrap: unset;
      width: 100%;
      white-space: pre-line;
    }

    pre {
      white-space: pre-wrap;
      white-space: -moz-pre-wrap;
      white-space: -pre-wrap;
      white-space: -o-pre-wrap;
      word-wrap: break-word;
    }
  }
</style>
<script>
  export default {
    async asyncData( {app, redirect, params} ) {
      let help = (await app.$api.get(`/help/${params.id}`));
      if (!help.status) {
        return {
          error: help
        }
      }
      return {
        page: help.data.page,
        error: null
      };
    },
  }
</script>
