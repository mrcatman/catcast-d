<template>
  <div class="list-item__sub">
    <slot></slot>
  </div>
</template>
<style lang="scss">
  .list-item {
    &__sub {
      font-size: .9375em;
      margin: 0 0 .25em;
      font-weight: 300;
    }
    &--transparent &__sub {
      opacity: .5;
    }
  }
</style>
