<template>
  <div class="settings-page">
    <div class="settings-page__menu">
      <div class="settings-page__menu__inner">
        <nuxt-link class="settings-page__menu__item" to="/settings/profile">{{$t('settings.menu.profile')}}</nuxt-link>
        <nuxt-link class="settings-page__menu__item" to="/settings/permissions">{{$t('settings.menu.permissions')}}</nuxt-link>
      </div>
    </div>
    <div class="settings-page__content">
      <div class="settings-page__content__inner">
        <nuxt-child></nuxt-child>
      </div>

    </div>
  </div>
</template>
<style lang="scss">
  .settings-page {
    display: flex;
    height: calc(100vh - 3.5em);
    overflow: hidden;

    &__menu {
      flex: 1;
      background: var(--title-box-color);
      padding: 2em;

      &__inner {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
      }

      &__item {
        text-decoration: none;
        padding: 1em 0;
        border-bottom: 2px solid rgba(255, 255, 255, .2);
        width: 100%;
        text-align: right;
        box-sizing: border-box;

        &.nuxt-link-active {
          border-bottom-color: var(--active-color);
        }
      }

    }

    &__content {
      flex: 2;
      padding: 2em;

      &__inner {
        max-width: 800px;
      }
    }
  }
</style>
<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'

  @Component({
    middleware: 'auth',
  })
  export default class SettingsPage extends Vue {

  }
</script>
