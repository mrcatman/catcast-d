<template>
<div class="left-sidebar" :class="{'left-sidebar--opened': sidebarOpened}">
	<div class="left-sidebar__links">
    <left-sidebar-link @click="onLinkClick" v-for="link in menuLinks" :link="link" :key="link.link" />

    <div class="left-sidebar__links__bottom">
      <left-sidebar-language-select />

      <!--
      <a @click="showAboutModal()" class="left-sidebar__link" :title="$t('about.heading')">
        <touch-ripple :speed="1" :opacity="0.3" color="#fff" transition="ease">
          <div class="left-sidebar__link__content">
            <div class="left-sidebar__icon"><i class="material-icons">info</i></div>
            <div class="left-sidebar__text">{{$t('about.heading')}}</div>
          </div>
        </touch-ripple>
      </a>
      -->
    </div>
	</div>
</div>
</template>
<style lang="scss">
.left-sidebar {
  width: 3.5em;
  min-width: 3.5em;
  height: calc(100vh - 3.5em);
  overflow: hidden;
  transition: width .4s;
  z-index: 10;
  border-right: 1px solid var(--input-border-color);
  background: var(--sidebar-bg);

  &--opened {
    width: 16em;
    min-width: 16em;
  }

  &__links {
    height: 100%;
    display: flex;
    flex-direction: column;

    &__bottom {
      display: flex;
      flex: 1;
      flex-direction: column;
      justify-content: flex-end;
    }
  }
  @media screen and (max-width: 768px) {
    position: fixed;
    z-index: 10000000;
    overflow: auto;
    bottom: unset;
    top: 3.5em;
    height: calc(100vh - 3.5em);
    left: -70vw;
    transition: all .2s;
    &--opened {
      left: 0;
    }

  }
}
</style>
<script>
import {mapGetters, mapMutations} from 'vuex';
import isMobile from "@/helpers/isMobile";
import {CHANNEL_TYPE_TV, CHANNEL_TYPE_RADIO} from "@/constants/entity-types";

import LeftSidebarLanguageSelect from '@/components/layout/left-sidebar/LeftSidebarLanguageSelect.vue';
import LeftSidebarLink from "@/components/layout/left-sidebar/LeftSidebarLink";

export default{
  components: {
    LeftSidebarLink,
    LeftSidebarLanguageSelect
  },
  computed: {
    ...mapGetters('config', ['allowedChannelTypes']),
    menuLinks() {
      return [
        this.allowedChannelTypes[CHANNEL_TYPE_TV] ? {link: '/tv', title: this.$t('channels.heading'), icon: 'tv'} : null,
        this.allowedChannelTypes[CHANNEL_TYPE_TV] ? {
          link: '/videos',
          title: this.$t('videos.title'),
          icon: 'video_library'
        } : null,
        this.allowedChannelTypes[CHANNEL_TYPE_RADIO] ? {
          link: '/radio',
          title: this.$t('radio.heading'),
          icon: 'radio'
        } : null,
        this.allowedChannelTypes[CHANNEL_TYPE_RADIO] ? {
          link: '/records',
          title: this.$t('records.title'),
          icon: 'audiotrack'
        } : null,
        //{link:'/news', title:this.$t('news.title'), icon:'format_list_bulleted'},
        //{link:'/playlists', title:this.$t('projects.title'), icon:'folder_open'},
        {link: '/announces', title: this.$t('announces.title'), icon: 'announcement'},
        {link: '/blog', title: this.$t('blog.heading'), icon: 'people'},
      ].filter(link => !!link);
    },
    sidebarOpened() {
      return this.$store.state.sidebarOpened;
    }
  },
	data() {
      return {
          aboutModalVisible: false,
      }
  },
	methods: {
      ...mapMutations(['TOGGLE_SIDEBAR']),
      onLinkClick() {
          if (isMobile()) {
              if (this.sidebarOpened) {
                  this.TOGGLE_SIDEBAR();
              }
          }
      },
      showAboutModal() {
          this.$emit('showAboutModal');
      }
  }
}
</script>
