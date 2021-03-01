<template>
  <div class="list-item__title">
    <slot></slot>
  </div>
</template>
<style lang="scss">
  .list-item {
    &__title {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      font-size: 1.25em;
      margin: 0 0 .25em;
      font-weight: 600;
    }
    &--transparent &__title {
      opacity: .5;
    }
  }

</style>
