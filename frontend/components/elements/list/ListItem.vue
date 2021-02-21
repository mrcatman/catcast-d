<template>
  <component :is="to ? 'router-link' : 'div'" :to="to" class="list-item" :class="{'list-item--link': to}">
    <slot></slot>
  </component>
</template>
<style lang="scss">
  .list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-decoration: none;
    height: 3em;
    font-size: 1em;
    padding: 1em 1.25em;
    border-bottom: 1px solid rgba(255, 255, 255, .1);
    transition: all .25s;
    position: relative;
    &--link {
      &:hover {
        background: rgba(255, 255, 255, .05);
      }
    }
  }
</style>
<script lang="ts">
  import Vue from 'vue';
  export default Vue.extend({
    props: {
      to: {
        type: String,
        required: false
      }
    }
  })
</script>
