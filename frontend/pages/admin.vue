<template>
  <div class="admin-panel-page">
    <div class="container page-form">
      <m-box :no-padding="true">
        <template v-slot:heading>{{$t('admin._title')}}</template>
        <template v-slot:default>
          <nuxt-child></nuxt-child>
        </template>
      </m-box>
    </div>
  </div>
</template>
<style lang="scss">
  .admin-panel-page {

  }
</style>
<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'

  @Component({
    middleware: 'admin',
  })
  export default class AdminPanelPage extends Vue {

  }
</script>
