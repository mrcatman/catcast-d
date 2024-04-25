<template>
  <component :is="tag">
    {{translatedMessage}}
  </component>
</template>
<script>
export default {
  computed: {
    translatedMessage() {
      return typeof this.message === 'string' ? this.$t(this.message) : this.$t(this.message.text, this.message.params);
    }
  },
  props: {
    message: {
      type: [String, Object],
      required: true
    },
    tag: {
      type: String,
      required: false,
      default: 'span'
    }
  }
}
</script>
