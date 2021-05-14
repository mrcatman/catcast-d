<template>
  <div class="controls-page admin-panel-page">
    <ControlsPageMenu>
      <nuxt-link class="controls-page__menu__item" to="/admin/frontend">{{$t('admin.menu.frontend')}}</nuxt-link>
      <nuxt-link class="controls-page__menu__item" to="/admin/federation">{{$t('admin.menu.federation')}}</nuxt-link>
      <nuxt-link class="controls-page__menu__item" to="/admin/users">{{$t('admin.menu.users')}}</nuxt-link>
    </ControlsPageMenu>
    <div class="controls-page__content">
      <nuxt-child class="controls-page__content__inner" :class="'admin-panel-page--' + $route.name.replace('admin-', '')"></nuxt-child>
    </div>
  </div>
</template>
<style lang="scss">
  .admin-panel-page {

  }
</style>
<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'
  import ControlsPageMenu from '~/components/layout/controls-page/ControlsPageMenu.vue'

  @Component({
    components: { ControlsPageMenu },
    middleware: 'admin',
  })
  export default class AdminPanelPage extends Vue {

  }
</script>
