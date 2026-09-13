<template>
  <modals-container-item
      v-for="modal in openedModals"
      :key="modal.id"
      :modal="modal"
  />
</template>
<script lang="ts" setup>
import { storeToRefs } from 'pinia';
import ModalsContainerItem from '@/components/ModalsContainerItem.vue';

const { openedModals } = storeToRefs(useModalsStore());
</script>
