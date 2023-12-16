<template>
<div class="user-panel">
  <div v-if="!loggedIn" class="buttons-row">
	  <c-button primary to="/auth/login">{{$t('auth.login')}}</c-button>
    <c-button primary to="/auth/register">{{$t('auth.register')}}</c-button>
  </div>
  <div v-else class="user-panel__info">
    <notifications-panel/>

    <div class="user-panel__button user-panel__profile">
      <div class="user-panel__avatar" :style="{backgroundImage: `url(${user.avatar})`}"></div>
      <div class="user-panel__username">{{user.username}}</div>

      <c-popup-menu position="bottom-left">
        <c-popup-menu-item to="/dashboard">{{$t('auth.dashboard')}}</c-popup-menu-item>
        <c-popup-menu-item @click="themesPanelVisible = true" >{{$t('themes._title')}}</c-popup-menu-item>
        <c-popup-menu-item :to="`/users/${user.id}`" >{{$t('auth.profile')}}</c-popup-menu-item>
        <c-popup-menu-item to="/user/settings">{{$t('auth.settings')}}</c-popup-menu-item>
        <c-popup-menu-item @click="logout()" >{{$t('auth.logout')}}</c-popup-menu-item>
      </c-popup-menu>

    </div>

 </div>
 <div class="user-panel__mobile" v-if="loggedIn">
   <div class="user-panel__mobile__avatar" @click="mobilePanelVisible = true"  :style="{backgroundImage:'url('+user.avatar+')'}"></div>
   <div class="user-panel__mobile__overlay" v-if="mobilePanelVisible">
     <div @click="mobilePanelVisible = false" class="user-panel__mobile__overlay__background"></div>
     <div class="user-panel__mobile__overlay__box">
       <div class="user-panel__mobile__overlay__header">
         <div class="user-panel__mobile__overlay__avatar"  :style="{backgroundImage:'url('+user.avatar+')'}"></div>
         <div class="user-panel__mobile__overlay__username">{{user.username}}</div>
       </div>
       <div class="user-panel__mobile__search">
         <SiteSearch @hide="onHideSearch"/>
       </div>
       <div class="user-panel__mobile__overlay__buttons">
         <notifications-panel @hide="onHideNotificationsPanel"/>

         <div @click="$router.push('/user/feed'); mobilePanelVisible = false" class="user-panel__info__button">
           <i class="fa fa-stream"></i>
         </div>
       </div>
       <div class="user-panel__mobile__overlay__links">
         <a @click="goAndCloseMenu('/dashboard')" class="user-panel__mobile__overlay__link">{{$t('auth.dashboard')}}</a>
         <!--<a @click="goAndCloseMenu('/studio')" class="user-panel__mobile__overlay__link">{{$t('auth.studio')}}</a>
         <a class="user-panel__mobile__overlay__link" @click="themesPanelVisible = true" >{{$t('themes._title')}}</a>-->
         <a @click="goAndCloseMenu('/users/'+user.id)" class="user-panel__mobile__overlay__link" >{{$t('auth.profile')}}</a>
         <a @click="goAndCloseMenu('/user/settings')" class="user-panel__mobile__overlay__link" >{{$t('auth.settings')}}</a>

         <a class="user-panel__mobile__overlay__link" @click="mobilePanelVisible = false; logout()" >{{$t('auth.logout')}}</a>
       </div>
     </div>
   </div>
 </div>
 <ThemesPanel v-if="themesPanelVisible" @close="themesPanelVisible = false" />
</div>
</template>
<style lang="scss">
.user-panel {
  color: var(--text-color);
  &__button {
    padding: 0 1em;
    cursor: pointer;
    display: flex;
    align-items: center;
    height: 100%;
    text-decoration: none;
    &:hover {
      background: var(--lighten-1);
    }
    &__count {
      position: absolute;
      top: 3em;
      left: 3em;
      background: var(--red);
      padding: 0 .25em;
      font-size: .75em;
      border-radius: .25em;
      font-weight: 600;
    }

  }
  &__info {
    position: relative;
    display: flex;
    align-items: stretch;

    &__buttons {
      display: flex;
      align-items: center;
    }
  }

  &__profile {
    padding: .75em;
    height: 2em;
    display: flex !important;
    align-items: center;
    cursor: pointer;
  }

  &__avatar {
    margin-right: .5em;
    width: 2em;
    height: 2em;
    background-size: contain;
    background-position: center center;
    background-repeat: no-repeat;
  }

  &__username {
    font-size: 1em;
    font-weight: 500;
  }

  &__mobile {
    display: none;
  }

  @media screen and (max-width: 768px) {
    & {
      &__auth-button {
        margin: 1.75em 1em 0 0;
      }

      &__info {
        display: none;
      }

      &__mobile {
        display: block;

        &__avatar {
          width: 2em;
          height: 2em;
          background-size: contain !important;
          background-repeat: no-repeat;
          margin: 0 1em 0 0;
        }

        &__overlay {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          z-index: 1000000;

          &__number {
            background: var(--red);
            font-weight: 600;
            padding: 0 .25em;
            margin: 0 0 0 .5em;
          }

          &__background {
            position: fixed;
            z-index: -1;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .75);
          }

          &__buttons {
            display: flex;
            align-items: center;
            justify-content: center;
          }

          &__box {
            position: relative;
            z-index: 100000;
            background: var(--box-color);
            width: auto;
          }

          &__avatar {
            width: 3em;
            height: 3em;
            background-size: contain !important;
            background-repeat: no-repeat !important;
          }

          &__header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1em 0;
          }

          &__link {
            display: block;
            text-decoration: none;
            padding: .5em;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
          }

          &__username {
            margin: 0 0 0 .5em;
            font-size: 1.25em;
            font-weight: 600;
          }
        }

        &__search {
          margin: 0 1em;
        }
      }
    }
  }
}
</style>
<script>
import { mapState } from 'vuex';
import ThemesPanel from '@/components/layout/ThemesPanel';
import NotificationsPanel from '@/components/layout/notifications/NotificationsPanel';
import SiteSearch from "@/components/layout/SiteSearch";

export default{
  components: {
    SiteSearch,
    NotificationsPanel,
    ThemesPanel
  },
  computed: mapState('auth', ['loggedIn', 'user']),
  data() {
    return {
      importantTicketModalVisible: true,
      mobilePanelVisible: false,
      themesPanelVisible: false,
    }
  },
  methods: {
    onHideSearch() {
      this.mobilePanelVisible = false;
    },
    onHideNotificationsPanel() {
      this.mobilePanelVisible = false;
    },
    goAndCloseMenu(link) {
      this.$router.push(link);
      this.mobilePanelVisible = false;
    },
    logout() {
      this.$store.dispatch('auth/logout');
      this.$router.push('/');
    }
  }
}
</script>
