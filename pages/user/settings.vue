<template>
  <layout-with-left-menu class="settings-page">
    <template slot="tabs">
      <c-tabs :vertical="!isMobile" :data="tabs" />
    </template>
    <template slot="main">
      <div class="page-container settings-page__container">
        <nuxt-child></nuxt-child>
      </div>
    </template>
  </layout-with-left-menu>
</template>
<style lang="scss">
.settings-page {
  &__container {
    padding: 0 2.5em;
    @media screen and (max-width: 768px) {
      padding: 0;
    }
  }
}
</style>
<script>
  import LayoutWithLeftMenu from "@/components/LayoutWithLeftMenu";
  import isMobile from "@/helpers/isMobile";
  export default {
    watch: {
      $route() {
        this.autoRedirect();
      }
    },
    mounted() {
      this.autoRedirect();
    },
    methods: {
      autoRedirect() {
        if (this.$route.name === 'user-settings') {
          this.$router.push(`/user/settings/personal`);
        }
      }
    },
    head() {
      return {
        title: this.$t('settings.heading')
      }
    },
    middleware: 'auth',
    components: {
      LayoutWithLeftMenu,
    },
    data() {
      return {
        tabs: [
          {id: 'personal', link: '/user/settings/personal', name: this.$t('settings.personal')},
          {id: 'password', link: '/user/settings/password', name: this.$t('settings.password.heading')},
          {id: 'notifications', link: '/user/settings/notifications', name: this.$t('settings.notifications')},
          {id: 'blacklist', link: '/user/settings/blacklist', name: this.$t('settings.blacklist')},
          {id: 'privacy', link: '/user/settings/privacy', name: this.$t('settings.privacy')},
          {id: 'social', link: '/user/settings/social', name: this.$t('settings.social')},
          {id: 'restore', link: '/user/settings/restore', name: this.$t('settings.restore.heading')},
        ],
        isMobile: isMobile(),
      }
    }
  }
</script>
