<template>
  <div class="list-item__buttons">
    <slot></slot>
  </div>
</template>
<style lang="scss">
  .list-item__buttons {
    margin-left: auto;
    @media screen and (max-width: 768px) {
      width: 100%;
      margin: 1em 0 0;
    }
  }
</style>
