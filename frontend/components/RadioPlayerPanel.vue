<template>
  <div class="radio-player-panel">
    <RadioPlaybackSwitchButton @switch="onSwitch" :channel="channel"/>
    <RadioPlayer ref="player" :channel="channel" :defaultView="true"/>
  </div>
</template>
<style lang="scss">
  .radio-player-panel {
    background: var(--box-element-color);
    display: flex;
    align-items: center;
    height: 6em;
    padding: 0 0 0 1.5em;
  }
</style>
<script>
  import RadioPlayer from "@/components/media-player/RadioPlayer";;
  import RadioPlaybackSwitchButton from "@/components/buttons/RadioPlaybackSwitchButton";
  export default {
    methods: {
      onSwitch(state) {
        this.$refs.player.update();
      }
    },
    props: {
      channel: {
        type: Object,
        required: true
      }
    },
    components: {RadioPlaybackSwitchButton, RadioPlayer}
  }
</script>
