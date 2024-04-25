<template>
  <div class="list-container">
    <slot></slot>
  </div>
</template>
<style lang="scss">
.list-container{
  background: var(--list-container-color);
  flex: 1;
  position: relative;
  display: flex;
  flex-direction: column;
  @media screen and (max-width: 768px) {
    font-size: .875em;
  }

  &__pager {
    padding: 1em;
  }

  &--with-pager &__inner {
    width: calc(100% - 5em);
  }

  &--with-pager .pager-vertical {
    position: absolute;
    height: 100%;
    display: flex;
    justify-content: center;
    right: 1em;
  }
}
.theme-modern .list-container {
  box-shadow: 0 .5em 1em -.5em var(--darken-2);
  &:before {
    content: "";
    display: block;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0), var(--lighten-1));
  }
  &__inner {
    box-shadow: 0.5em 0.5em 1.75em -0.5em inset var(--darken-1);
    position: relative;
    z-index: 1;
  }
}

</style>
<script>
export default {

}
</script>
