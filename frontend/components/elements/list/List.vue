<template>
  <div class="list">
    <div class="list__inner" :class="[cols ? 'list__inner--multicol' : '', 'list__inner--multicol-' + cols]">
      <slot></slot>
    </div>
  </div>
</template>
<style lang="scss">
  .list {
    background: rgba(0,0,0,.25);
    flex: 1;
    position: relative;
    display: flex;
    flex-direction: column;
    &__inner {
      flex: 1;
      overflow-x: hidden;
      overflow-y: auto;
      box-shadow: inset 0.5em 0.5em 1.75em -0.5em rgba(0,0,0,.16);
      position: relative;
      z-index: 1;
      &--multicol {
        display: flex;
        flex-wrap: wrap;
        > .list-item {
          border-bottom: none;
        }
        &-2 > .list-item {
          width: 50%;
        }
        &-3 > .list-item {
          width: calc(100% / 3);
        }
        &-4 > .list-item {
          width: 25%;
        }
      }
    }
  }
</style>
<script>
export default {
  props: {
    cols: {
      type: Number,
      required: false
    }
  }
}
</script>
