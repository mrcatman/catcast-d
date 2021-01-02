<template>
  <div class="header">
    <nuxt-link class="header__logo" to="/">
      <img src="/assets/pictures/logo-big.svg" class="header__logo__picture" />
    </nuxt-link>
    <UserPanel />
  </div>
</template>
<script>
  import UserPanel from './UserPanel'
  export default{
    name: 'HeaderNav',
    components: { UserPanel }
  }
</script>
<style lang="scss">
  .header{
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    padding: .5em;
    height: 3.5em;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 10000000;
    background: var(--sidebar-bg);
    &__logo {
      margin: 0 1.25em;
      &__picture{
        height: 2.5em;
        opacity: .875;
        transition: all .25s;
        &:hover {
          opacity: 1;
        }
      }
    }
  }

</style>
