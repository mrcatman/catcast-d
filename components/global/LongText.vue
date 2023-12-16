<template>
  <div class="long-text" v-html="textHTML"></div>
</template>
<script>
import marked from "marked";

export default {
  computed: {
    textHTML() {
      return marked.parse(this.text);
    }
  },
  props: {
    text: {
      type: String,
      required: true
    }
  }
}
</script>
<style lang="scss" scoped>
.long-text {
  line-height: 1.4;
  max-height: 50vh;
  overflow: auto;
  margin-top: 1em;
  background: var(--lighten-1);
  border: 1px solid var(--border-color);
  padding: 1em;
  ::v-deep p:last-of-type {
    margin-bottom: 0;
  }
}
</style>
