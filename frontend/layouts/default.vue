<template>
  <div id="app" class="app">
    <HeaderNav />
    <div class="app__container">
      <nuxt/>
      <Sidebar v-if="isLoggedIn" />
    </div>
    <notifications group="all"  position="bottom left" />
    <portal-target name="modals"></portal-target>
  </div>
</template>
<style lang="scss">
  @import "~/assets/styles/global.scss";
  .app {
    &__container {
      display: flex;
      margin-top: 3.5em;
      height: calc(100vh - 3.5em);
      overflow: auto;
      > * {
        flex: 1;
      }
    }
  }
</style>
<script lang="ts">
  import Sidebar from '~/components/layout/Sidebar.vue'
  declare const window: any;

  import Vue from 'vue'
  import HeaderNav from '~/components/layout/header/HeaderNav.vue'
  export default Vue.extend({
    components: { Sidebar, HeaderNav },
    computed: {

      isLoggedIn() {
        return this.$accessor.modules.auth.isLoggedIn;
      }
    },
    mounted() {
      window.$t = this.$t; // non-elegant but working
    }
  })
</script>
