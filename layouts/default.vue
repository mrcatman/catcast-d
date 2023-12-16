<template>
  <div id="app" class="theme" :class="'theme-'+currentTheme">
    <main class="main" :class="{'main--sidebar-opened': sidebarOpened}">
      <left-sidebar />
      <div class="content">
        <nuxt/>
      </div>
    </main>

    <audio id="audio" style="display:none"/>

    <top-bar/>

    <attachments-modal />
    <standard-modal />

    <alerts />
    <on-site-notifications />

    <video-players />

    <media-uploader v-if="loggedIn" />

    <inline-player />
  </div>
</template>

<script>
import { mapState } from 'vuex';
import LeftSidebar from '@/components/layout/left-sidebar/LeftSidebar';
import TopBar from '@/components/layout/TopBar';

import Alerts from '@/components/layout/Alerts';
import OnSiteNotifications from '@/components/layout/OnSiteNotifications';

import InlinePlayer from '@/components/layout/InlinePlayer';
import VideoPlayers from '@/components/layout/VideoPlayers';

import AttachmentsModal from "@/components/attachments/AttachmentsModal";
import StandardModal from "@/components/StandardModal";

import MediaUploader from "@/components/MediaUploader";

export default {
  components: {
    MediaUploader,
    VideoPlayers,
    AttachmentsModal,
    StandardModal,
    TopBar,
    LeftSidebar,
    InlinePlayer,
    OnSiteNotifications,
    Alerts
  },
  computed: {
    ...mapState('auth', ['loggedIn', 'user']),
    ...mapState(['sidebarOpened', 'currentTheme'])
  },
  mounted() {
    if (this.loggedIn) {
      this.$echo.private(`App.User.${this.user.id}`).notification((notification) => {
        this.$store.dispatch('notifications/create', notification);
        this.$store.dispatch('auth/incrementNotificationsCount');
      })
    }
  }
}
</script>
<style lang="scss" scoped>
.main {
  display: flex;
  background: var(--main-bg);
  color: var(--text-color);
  padding-top: 3.5em;
  height: calc(100vh - 3.5em);
}
.content {
  flex: 1;
  height: calc(100vh - 3.5em);
  overflow: auto;
}
</style>
