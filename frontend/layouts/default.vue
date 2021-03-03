<template>
  <div id="app" class="app">
    <HeaderNav />
    <div class="app__container">
      <nuxt/>
    </div>
    <notifications group="all"  position="bottom left" />
    <portal-target name="modals"></portal-target>
  </div>
</template>
<style lang="scss">
  @import "~/assets/styles/global.scss";
  .app {
    &__container {
      margin-top: 3.5em;
      height: calc(100vh - 3.5em);
      overflow: auto;
    }
  }
</style>
<script lang="ts">
  declare const window: any;

  import Vue from 'vue'
  import HeaderNav from '~/components/layout/header/HeaderNav.vue'
  export default Vue.extend({
    components: { HeaderNav },
    mounted() {
      window.$t = this.$t; // non-elegant but working
    }
  })
</script>
