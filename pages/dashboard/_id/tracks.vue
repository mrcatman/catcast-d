<template>
  <div class="dashboard__files-list">
    <AudioManager :dragHandles="false" :showPlaylistButtons="false" :showControlButtons="true" :showTopPanel="true" :showEditButtons="true" :drakeName="'dashboard_tracks'" :channel="channel"/>
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
<script>
import AudioManager from '@/components/AudioManager';
export default {
  created() {
    const service = this.$dragula.$service;
    service.options('dashboard_tracks', {
      moves: function (el, container, handle) {
        return false;
        return (handle.classList.contains('audio-track__handle') || handle.parentElement.classList.contains('audio-track__handle'));
      }
    })
  },
  head() {
    return {
      title: this.$t('dashboard.tracks._title')
    }
  },
  components: {
    AudioManager
  },
  props: {
    channel: {
      type: Object,
      required: true
    }
  },
}
</script>
