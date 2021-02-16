<template>
<div class="box__unset-padding">
  <m-tabs :list="tabs" v-model="currentTab" />
  <div v-if="config && config[currentTab]">
    {{config[currentTab]}}
  </div>
</div>
</template>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { SiteGetFullConfig } from '~/api/modules/site'
import TabData from '~/components/types/Tabs'

@Component()
export default class AdminConfigPage extends Vue {
  config = null;
  currentTab = 'frontend';
  tabs = [
    {id: 'frontend', name: this.$t('admin.config.tabs.frontend') as string},
    {id: 'federation', name: this.$t('admin.config.tabs.federation') as string},
  ] as Array<TabData>;

  async fetch() {
    this.config = await SiteGetFullConfig();
  }
}
</script>
