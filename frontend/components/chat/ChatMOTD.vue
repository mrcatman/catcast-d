<template>
  <div class="chat__motd" v-if="showMOTD">
    <span class="chat__motd__text">
      {{motd}}
    </span>
    <c-button transparent narrow icon-only icon="close" @click="showMOTD = false" />
  </div>
</template>
<style lang="scss" scoped>
.chat {
  &__motd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--channel-colors-inside-panels);
    margin: .5em;
    padding: 0 .5em;
    border-radius: .5em;
    font-size: 1.125em;
    font-weight: bold;
  }

}
</style>
<script>
export default {
  data() {
    return {
      showMOTD: this.value
    }
  },
  watch: {
    showMOTD(showMOTD) {
      this.$emit('input', showMOTD);
    },
    value(showMOTD) {
      this.showMOTD = showMOTD;
    }
  },
  props: {
    value: {
      type: Boolean,
      required: true
    },
    motd: {
      type: String,
      required: true
    }
  }
}
</script>
