<template>
  <div class="controls-page">
    <ControlsPageMenu>
      <nuxt-link class="controls-page__menu__item" to="/settings/profile">{{$t('settings.menu.profile')}}</nuxt-link>
      <nuxt-link class="controls-page__menu__item" to="/settings/permissions">{{$t('settings.menu.permissions')}}</nuxt-link>
    </ControlsPageMenu>
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
  import ControlsPageMenu from '~/components/layout/controls-page/ControlsPageMenu.vue'

  @Component({
    components: { ControlsPageMenu },
    middleware: 'auth',
  })
  export default class SettingsPage extends Vue {

  }
</script>
