<template>
  <div class="left-sidebar" :class="{'left-sidebar--opened': sidebarOpened}">
    <preloading-line v-if="loading" v-for="i in 10" one-height />
    <div v-else class="left-sidebar__menu">
      <div v-for="submenu in menu" class="left-sidebar__submenu">
        <div class="left-sidebar__submenu__heading">{{ $t(submenu.heading) }}</div>
        <left-sidebar-item @click="onLinkClick" v-for="item in submenu.children" :item="item" :key="item.url"/>
      </div>
    </div>

    <div class="left-sidebar__bottom">
      <left-sidebar-language-select/>

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
</template>
<script lang="ts" setup>
import { storeToRefs } from "pinia";

import isMobile from "@/helpers/isMobile";

import LeftSidebarLanguageSelect from '@/components/layout/left-sidebar/LeftSidebarLanguageSelect.vue';
import LeftSidebarItem from "@/components/layout/left-sidebar/LeftSidebarItem";
import PreloadingLine from "@/components/preloading/PreloadingLine.vue";

const { useRequest } = useApi();

const { data: menu, loading } = useRequest('/directory/menu');

const sidebar = useSidebarStore();
const { toggleSidebar } = sidebar;
const { sidebarOpened } = storeToRefs(sidebar);

const onLinkClick = () => {
  if (isMobile() && sidebarOpened.value) {
    toggleSidebar();
  }
}

</script>
<style lang="scss">
.left-sidebar {
  width: 3.5em;
  min-width: 3.5em;
  height: calc(100vh - 3.5em);
  overflow: hidden;
  transition: all .4s;
  z-index: 10;
  border-right: 1px solid var(--border-color);
  background: var(--sidebar-color);
  display: flex;
  flex-direction: column;

  &--opened {
    width: 16em;
    min-width: 16em;
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

  &__submenu {
    border-bottom: 1px solid var(--border-color);
    &__heading {
      height: 0;
      overflow: hidden;
      padding: 0 1em;
      transition: height .4s;
      line-height: 3.5em;
    }
  }

  &--opened &__submenu__heading {
    height: 3em;
  }
  &__bottom {
    display: flex;
    flex: 1;
    flex-direction: column;
    justify-content: flex-end;
  }
}
</style>

