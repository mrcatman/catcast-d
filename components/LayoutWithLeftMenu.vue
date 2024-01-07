<template>
  <div class="inner-scroll layout-with-left-menu">
     <div class="layout-with-left-menu__sidebar">
       <slot name="tabs"></slot>
     </div>
    <div class="layout-with-left-menu__content" ref="content">
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
}
</style>
<script>
export default {
  methods: {
    scrollToTop() {
      this.$refs.content.scrollTop = 0;
    }
  }
}
</script>
