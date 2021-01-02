<template>
   <m-modal v-model="visible" >
     <m-tabs :list="tabs" v-model="currentTab" />
     <transition name="fade" mode="out-in">
       <Login @auth="onAuth" v-if="currentTab === 'login'" />
       <Register @auth="onAuth" v-if="currentTab === 'register'" />
     </transition>
   </m-modal>
</template>
<script lang="ts">
  import Component, { mixins } from 'vue-class-component';
  import BaseModalComponent from '~/components/types/BaseModalComponent'
  import TabData from '~/components/types/Tabs'
  import Login from '../auth/Login.vue'
  import Register from '../auth/Register.vue'
  import User from '~/types/User'

  @Component({
    components: {
      Login,
      Register
    }
  })
  export default class AuthModal extends mixins(BaseModalComponent) {
    onAuth(user: User) {
      this.$accessor.modules.auth.setUser(user);
      this.visible = false;
    }
    currentTab = 'login';
    tabs = [] as Array<TabData>;
    mounted(): void {
      this.tabs = [
        {id: 'login', name: this.$t('auth.login')},
        {id: 'register', name: this.$t('auth.register')},
      ]
    }
  };
</script>
