<template>
  <div class="controls-page">
    <div class="controls-page__menu">
      <div class="controls-page__menu__inner">
        <nuxt-link class="controls-page__menu__item" to="/settings/profile">{{$t('settings.menu.profile')}}</nuxt-link>
        <nuxt-link class="controls-page__menu__item" to="/settings/permissions">{{$t('settings.menu.permissions')}}</nuxt-link>
      </div>
    </div>
    <div class="controls-page__content">
      <div class="controls-page__content__inner">
        <nuxt-child></nuxt-child>
      </div>
    </div>
  </div>
</template>
<style lang="scss">

</style>
<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'

  @Component({
    middleware: 'auth',
  })
  export default class SettingsPage extends Vue {

  }
</script>
