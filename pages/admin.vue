<template>
  <layout-with-left-menu>
    <template slot="tabs">

    </template>
    <template slot="main">
      <nuxt-child  />
    </template>
  </layout-with-left-menu>
    <!--
    <div class="admin-panel__tabs">
       <nuxt-link class="admin-panel__tab" :class="{'admin-panel__tab--active': '/admin/'+tab.link === $route.path}" :to="'/admin/'+tab.link" :key="tab.link" v-for="tab in tabs">{{$t(tab.name)}}</nuxt-link>
    </div>

  </div> -->
</template>
<style lang="scss">

</style>
<script>
  import LayoutWithLeftMenu from "../components/LayoutWithLeftMenu";
  export default {
    components: {LayoutWithLeftMenu},
    computed: {
      tabs() {
        return [
          {
            name: 'admin.tabs.support',
            link: 'support',
          },
          {
            name: 'admin.tabs.channels',
            link: 'channels',
          },
          {
            name: 'admin.tabs.users',
            link: 'users',
          },
          {
            name: 'admin.tabs.donates',
            link: 'donates',
          },
          {
            name: 'admin.tabs.servers',
            link: 'servers',
          },
          {
            name: 'admin.tabs.help',
            link: 'help',
          },
          {
            name: 'admin.tabs.languages',
            link: 'languages',
          }
        ]
      },
    },

  }
</script>
