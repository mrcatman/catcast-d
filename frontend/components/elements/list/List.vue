<template>
  <div class="list">
    <div class="list__inner">
      <slot></slot>
    </div>
  </div>
</template>
<style lang="scss">
  .list {
    background: rgba(0,0,0,.1);
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
    }
  }
</style>
