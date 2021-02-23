<template>
  <div class="box">
    <div class="box__heading">
      <slot name="heading"></slot>
    </div>
    <div class="box__content" :class="{'box__content--no-padding': noPadding}">
      <slot></slot>
    </div>

  </div>
</template>
<script>
export default {
  props: {
    noPadding: Boolean
  }
}
</script>
<style lang="scss">
  .box {
    display: block;
    text-decoration: none;
    position: relative;
    color: var(--text-color);
    background: var(--box-color);
    box-shadow: 0 0.5em 1em -0.75em rgba(0,0,0,.5);
    &--in-list {
      margin-top: 1em;
    }
    &__heading {
      font-size: 1.25em;
      font-weight: bold;
      padding: .5em .75em;
      border-bottom: 1px solid rgba(255,255,255,.1);
      background: var(--box-header-color);
      color: var(--active-color);
      &:empty {
        display: none;
      }
    }
    &__content {
      padding: 1em;
      &--no-padding {
        padding: 0;
      }
    }
  }
</style>
