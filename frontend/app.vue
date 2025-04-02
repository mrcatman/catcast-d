<template>
  <div>
    <NuxtLoadingIndicator />
    <NuxtLayout>
      <NuxtPage/>
    </NuxtLayout>
  </div>
</template>
<script lang="ts" setup>
// todo: fix style
</script>
<style>
.nuxt-loading-indicator {
  z-index: 99999999!important;
  background: #107fd0!important;
}
</style>
