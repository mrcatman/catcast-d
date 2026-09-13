<template>
  <div id="app" class="theme" :class="'theme-' + theme">
    <div class="minimal-layout">
      <audio id="audio" style="display:none"/>
      <alerts/>
      <modals-container/>
      <slot/>
    </div>
  </div>
</template>
<script lang="ts" setup>
import Alerts from '@/components/layout/Alerts.vue';
import ModalsContainer from '@/components/ModalsContainer.vue';

const { theme } = useThemeStore();
</script>
<style lang="scss">
.minimal-layout {
  width: 100vw;
  height: 100vh;
  position: relative;
}
</style>
