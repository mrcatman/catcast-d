<template>
  <div class="auth-panel">
    <AuthModal v-model="modalVisible" />
    <m-button v-if="!isLoggedIn" @click="modalVisible = !modalVisible">{{$t('auth.login_or_register')}}</m-button>
    <div v-else class="auth-panel__inner">
      <div class="auth-panel__avatar" v-if="avatar" :style="{backgroundImage: `url(${avatar})`}"></div>
      <div class="auth-panel__username">{{username}}</div>
      <m-popup-menu class="popup-menu-bottom popup-menu-bottom-left">
        <m-popup-menu-item  v-if="isAdmin" to="/admin">{{$t('auth.menu.admin_panel')}}</m-popup-menu-item>
        <m-popup-menu-item to="/dashboard">{{$t('auth.menu.dashboard')}}</m-popup-menu-item>
        <m-popup-menu-item :to="`/user/${id}`" >{{$t('auth.menu.profile')}}</m-popup-menu-item>
        <m-popup-menu-item to="/settings/profile">{{$t('auth.menu.settings')}}</m-popup-menu-item>
        <m-popup-menu-item @click="logout()" >{{$t('auth.menu.logout')}}</m-popup-menu-item>
      </m-popup-menu>
    </div>
  </div>
</template>
<style lang="scss">
  .auth-panel {
    margin: 0 0 0 1em;
  }
</style>
<script lang="ts">
  import Vue from 'vue'
  import AuthModal from './AuthModal.vue'
  export default Vue.extend({
    computed: {
      isAdmin() {
        return this.$accessor.modules.auth.isAdmin;
      },
      avatar() {
        return this.$accessor.modules.auth?.me?.avatar?.full_url;
      },
      id() {
        return this.$accessor.modules.auth.me ? this.$accessor.modules.auth.me.id : null;
      },
      username() {
        return this.$accessor.modules.auth.me ? this.$accessor.modules.auth.me.login : null;
      },
      isLoggedIn() {
        return this.$accessor.modules.auth.isLoggedIn;
      }
    },
    name: 'AuthPanel',
    components: { AuthModal },
    methods: {
      logout() {
        this.$accessor.modules.auth.logout();
      }
    },
    data () {
      return {
        modalVisible: false
      }
    },
  })
</script>
<style lang="scss">
  .auth-panel {
    &__username {
      font-weight: 500;
    }

    &__avatar {
      margin: 0 .5em 0 0;
      width: 3em;
      height: 3em;
      background-size: contain;
      background-position: 50%;
      background-repeat: no-repeat;
    }

    &__inner {
      display: flex;
      align-items: center;
    }
  }
</style>
