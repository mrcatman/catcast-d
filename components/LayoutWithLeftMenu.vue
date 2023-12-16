<template>
  <div class="inner-scroll layout-with-left-menu">
     <div class="layout-with-left-menu__sidebar">
       <slot name="tabs"></slot>
     </div>
    <div class="layout-with-left-menu__content">
      <div class="page-container layout-with-left-menu__content__outer">
        <slot name="main"></slot>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
.layout-with-left-menu {
  display: flex;
  align-items: stretch;
  height: 100%;
  overflow: hidden;
  @media screen and (max-width: 768px) {
    flex-direction: column;
  }
  &__sidebar {
    position: relative;
    background: var(--box-footer-color);
    display: flex;
    flex-direction: column;
    justify-content: center;
 }
  &__content {
    flex: 3;
    overflow: auto;
    &__outer {
      display: flex;
      flex-direction: column;
      justify-content: center;
      width: 100%;
      min-height: 100%;
      flex: 1;
      padding: 1em;
      @media screen and (max-width: 768px) {
        min-height: unset;
        padding: 0;
      }
    }
  }
  &__header{
    margin: 2em 0 .5em;
    font-size: 1.5em;
    font-weight: 400;
    &:first-of-type {
      margin-top: 0;
    }
    &--small {
      font-size: 1.25em;
      @media screen and (max-width: 768px) {
        margin: 0 0 .5em;
      }
    }
  }


}
</style>
