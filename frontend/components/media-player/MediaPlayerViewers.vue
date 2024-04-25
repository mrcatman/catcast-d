<template>
  <div v-if="broadcastViewers" class="media-player__viewers">
    <c-icon icon="fa-eye" class="media-player__viewers__icon" />
    <span class="media-player__viewers__count">
      {{broadcastViewers}}
    </span>
  </div>
</template>
<script>
export default {
  computed: {
    broadcastViewers() {
      if (this.broadcast) {
        return this.broadcast.viewers;
      }
      return null;
    }
  },
  props: {
    broadcast: {
      type: Object,
      required: false
    }
  }
}
</script>
