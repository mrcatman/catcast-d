<template>
  <div class="controls-page__menu" :class="{'controls-page__menu--visible': visible}">
    <div class="controls-page__menu__toggle" v-if="!visible" @click="visible = true">
      <i class="material-icons">chevron_right</i>
    </div>
    <div class="controls-page__menu__inner">
      <slot></slot>
    </div>
  </div>
</template>
<script>
export default {
  name: 'ControlsPageMenu',
  watch: {
    $route() {
      this.visible = false;
    }
  },
  data() {
    return {
      visible: false
    }
  }
}
</script>
