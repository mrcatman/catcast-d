<template>
  <nuxt-link v-if="data" :to="data.id !== 'index' ? `/directory/${data.id}` : '/'" class="left-sidebar__link">
    <c-tooltip v-if="!sidebarOpened" position="center-right">{{$t(data.heading)}}</c-tooltip>
    <div v-if="data.icon" class="left-sidebar__link__icon-container">
      <c-icon :icon="data.icon" class="left-sidebar__link__icon"/>
    </div>
    <div class="left-sidebar__link__title">{{$t(data.heading)}}</div>
  </nuxt-link>
</template>
<script>
import {mapState} from "vuex";

export default {
  computed: {
    ...mapState(['sidebarOpened']),
  },
  props: {
    data: {
      type: Object,
      required: true
    }
  }
}
</script>
<style lang="scss" scoped>
.left-sidebar__link {
  width: 100%;
  height: 3.5em;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  cursor: pointer;
  transition: background-color .2s;
  color: var(--link-color);
  &:hover {
    background: var(--lighten-1);
  }
  &.nuxt-link-exact-active {
    background: var(--active-color);
  }
  &__title {
    white-space: nowrap;
    margin-left: .5em;
    font-weight: 400;
  }
  &__icon-container {
    padding: 0 1em;
    width: 1.5em;
    height: 1.5em;
  }
  &__icon {
    font-size: 1.5em;
  }
}
</style>
