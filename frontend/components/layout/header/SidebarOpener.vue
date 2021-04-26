<template>
  <div class="sidebar-opener">
    <m-button @click="opened = !opened" rounded flat icon="menu"/>
  </div>
</template>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { Watch } from 'vue-property-decorator'

@Component({components: {}})
export default class Sidebar extends Vue {
  opened: boolean = this.$accessor.modules.sidebar.opened;

  @Watch('opened')
  onVisibilityChange(isOpened: boolean) {
    this.$accessor.modules.sidebar.setOpened(isOpened);
  }



}
</script>
<style lang="scss">
  .sidebar-opener {
    margin: 0 .25em;
  }
</style>
