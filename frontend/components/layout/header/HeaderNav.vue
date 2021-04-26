<template>
  <div class="header">
    <nuxt-link :title="config.site_name" class="header__logo" to="/">
      <img v-if="config.logo" :alt="config.site_name" :src="config.logo" class="header__logo__picture" />
      <span v-else class="header__logo__text">{{config.site_name}}</span>
    </nuxt-link>
    <SearchBox />
    <UserPanel />
    <SidebarOpener />
  </div>
</template>
<script>
  import UserPanel from './UserPanel'
  import SearchBox from '~/components/layout/header/SearchBox'
  import SidebarOpener from '@/components/layout/header/SidebarOpener'
  export default{
    name: 'HeaderNav',
    computed: {
      config() {
        return this.$accessor.modules.site?.config || {};
      },
    },
    components: { SidebarOpener, SearchBox, UserPanel }
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
      margin: 0 1.25em 0 .5em;
      text-decoration: none;
      &__text {
        font-size: 1.25em;
        font-weight: bold;
      }
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
