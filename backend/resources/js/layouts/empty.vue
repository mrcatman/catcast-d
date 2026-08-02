<template>
  <div id="app" class="theme" :class="'theme-'+currentTheme">
    <div class="minimal-layout">
      <audio id="audio" style="display:none"/>
      <alerts />
      <nuxt/>
    </div>
  </div>
</template>
<script>
  import { mapState } from 'vuex';
  import Alerts from '@/components/layout/Alerts';
  import StandardModal from "@/components/StandardModal";
  export default{
    components: {StandardModal, Alerts},
    computed: mapState(['currentTheme']),
  }
</script>
<style lang="scss">
  .minimal-layout {
    width: 100vw;
    height: 100vh;
    position: relative;
  }
</style>
