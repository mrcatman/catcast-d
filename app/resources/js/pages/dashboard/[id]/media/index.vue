<template>
  <media-manager :channel="channel"/>
</template>
<style lang="scss">

</style>
<script lang="ts" setup>
import MediaManager from '@/components/dashboard/MediaManager.vue';

const { t } = useI18n();

defineProps<{
  channel: Channels.Item;
}>();

useHead(() => ({
  title: t('dashboard.media.heading')
}));
</script>
