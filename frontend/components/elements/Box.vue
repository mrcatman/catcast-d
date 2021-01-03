<template>
  <div class="box">
    <div class="box__heading">
      <slot name="heading"></slot>
    </div>
    <div class="box__content">
      <slot></slot>
    </div>

  </div>
</template>
<style lang="scss">
  .box {
    display: block;
    text-decoration: none;
    position: relative;
    color: var(--text-color);
    background: var(--box-color);
    box-shadow: 0 0.5em 1em -0.75em rgba(0,0,0,.5);
    &__heading {
      font-size: 1.25em;
      font-weight: bold;
      padding: 1em;
      border-bottom: 1px solid rgba(255,255,255,.1);
      &:empty {
        display: none;
      }
    }
    &__content {
      padding: 1em;
    }
  }
</style>
