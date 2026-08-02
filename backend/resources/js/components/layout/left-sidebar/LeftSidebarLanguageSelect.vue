<template>
  <left-sidebar-item  @click="changeLanguage()" :item="{heading: 'TODO'}" />
</template>
<script lang="ts" setup>
import LeftSidebarItem from "@/components/layout/left-sidebar/LeftSidebarItem";

const  changeLanguage = () => {
  // todo: locale selector
  // let localeIndex = this.locales.indexOf(this.locale);
  // if (localeIndex === this.locales.length - 1) {
  //   localeIndex = 0;
  // } else {
  //   localeIndex++;
  // }
  // let locale = this.locales[localeIndex];
  // this.$store.commit('SET_LOCALE', locale);
  // localStorage.locale = locale;
  // this.$i18n.locale = locale;
}
</script>
