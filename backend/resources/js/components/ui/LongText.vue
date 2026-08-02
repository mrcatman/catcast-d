<template>
  <div class="long-text" v-html="textHTML"></div>
</template>
<script lang="ts" setup>
import { marked } from "marked";
const props = withDefaults(defineProps<{
  text?: string;
}>(), {
  text: ''
});

const textHTML = marked.parse(props.text)
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
