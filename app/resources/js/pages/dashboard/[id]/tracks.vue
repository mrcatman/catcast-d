<template>
  <div class="dashboard__files-list">
    <audio-manager
        :drag-handles="false"
        :show-playlist-buttons="false"
        :show-control-buttons="true"
        :show-top-panel="true"
        :show-edit-buttons="true"
        drake-name="dashboard_tracks"
        :channel="channel"
    />
  </div>
</template>
<style lang="scss">
.dashboard {
  &__files-list {
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
  }
}
</style>
<script lang="ts" setup>
import AudioManager from '@/components/AudioManager.vue';

const { t } = useI18n();

defineProps<{
  channel: Channels.Item;
}>();

useHead(() => ({
  title: t('dashboard.tracks.heading')
}));
</script>
