<template>
  <component :is="to ? 'router-link' : 'div'" :to="to" class="list-item" :class="{'list-item--link': to, 'list-item--transparent': transparent}">
    <slot></slot>
  </component>
</template>
<style lang="scss">
  .list-item {
    box-sizing: border-box;
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-decoration: none;
    min-height: 3em;
    font-size: 1em;
    padding: .875em;
    border-bottom: 1px solid rgba(255, 255, 255, .1);
    transition: all .25s;
    position: relative;

    &--link {
      &:hover {
        background: rgba(255, 255, 255, .05);
      }
    }
    &__inner-link {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex: 1;
      text-decoration: none;
      margin: -.875em;
      padding: .875em;
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
      transparent: {
        type: Boolean,
        required: false
      },
      to: {
        type: String,
        required: false
      }
    }
  })
</script>
