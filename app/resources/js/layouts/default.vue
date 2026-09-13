<template>
  <div id="app" class="theme" :class="'theme-' + theme">
    <main class="main" :class="{'main--sidebar-opened': sidebarOpened}">
      <left-sidebar />
      <div class="content" ref="content">
        <slot />
      </div>
    </main>

    <audio id="audio" style="display:none"/>

    <top-bar/>

      <!--
    <attachments-modal />
    -->
    <modals-container />

    <alerts />
    <on-site-notifications />

    <video-players />

    <!--<media-uploader v-if="loggedIn" />

    <inline-player /> TODO: uncomment after Pinia integration -->
  </div>
</template>

<script lang="ts" setup>
import { storeToRefs } from "pinia";
import LeftSidebar from '@/components/layout/left-sidebar/LeftSidebar';
import TopBar from '@/components/layout/TopBar';
import Alerts from '@/components/layout/Alerts';
import OnSiteNotifications from '@/components/layout/OnSiteNotifications';
import InlinePlayer from '@/components/layout/InlinePlayer';
import VideoPlayers from '@/components/layout/VideoPlayers';
import AttachmentsModal from "@/components/attachments/AttachmentsModal";
import ModalsContainer from "@/components/ModalsContainer";
import MediaUploader from "@/components/MediaUploader";

const route = useRoute();

const { user, loggedIn } = useAuthStore();

const sidebar = useSidebarStore();
const { sidebarOpened } = storeToRefs(sidebar);

const { siteName } = useConfigStore();
const { theme } = useThemeStore();

const contentRef = useTemplateRef('content')

watch(route, (newRoute, oldRoute) => {
  if (newRoute.path !== oldRoute.path) {
    contentRef.scrollTop = 0;
  }
})

useHead({
  titleTemplate: (title) => {
    if (!title) {
      return siteName;
    }
    return `${title} | ${siteName}`;
  }
})

// if (this.loggedIn) {
//   this.$echo.private(`App.User.${this.user.id}`).notification((notification) => {
//     this.$store.dispatch('notifications/create', notification);
//     this.$store.dispatch('auth/incrementNotificationsCount');
//   })
// }
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
