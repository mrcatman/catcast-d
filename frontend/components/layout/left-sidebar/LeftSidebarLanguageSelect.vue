<template>
  <left-sidebar-link  @click="changeLanguage()" :link="{title: $t('_language_name'), icon_text: locale}" />
</template>
<script>
import LeftSidebarLink from "@/components/layout/left-sidebar/LeftSidebarLink.vue";


import {mapState} from 'vuex';

export default {
  components: {LeftSidebarLink},

  methods: {
    changeLanguage() {
      let localeIndex = this.locales.indexOf(this.locale);
      if (localeIndex === this.locales.length - 1) {
        localeIndex = 0;
      } else {
        localeIndex++;
      }
      let locale = this.locales[localeIndex];
      this.$store.commit('SET_LOCALE', locale);
      localStorage.locale = locale;
      this.$i18n.locale = locale;
    }
  },
  props: ['full'],
  computed: {
    ...mapState(['locale']),
    locales() {
      return Object.keys(this.$i18n.messages);
    }
  }
}
</script>
