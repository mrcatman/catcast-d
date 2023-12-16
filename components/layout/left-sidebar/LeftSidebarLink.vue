<template>
  <component :is="link.link ? 'nuxt-link' : 'div'" @click="$emit('click')" :to="link.link" class="left-sidebar__link" :key="link.title" :title="link.title">
    <div class="left-sidebar__link__icon">
      <c-icon  v-if="link.icon" :icon="link.icon" />
      <span v-if="link.icon_text" class="left-sidebar__link__icon__text">{{link.icon_text}}</span>
    </div>

    <div class="left-sidebar__link__title">{{link.title}}</div>
  </component>
</template>
<script>
export default {
  props: {
    link: {
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
  &__icon {
    padding: 0 1em;
    width: 1.5em;
    height: 1.5em;

    &__text {
      font-weight: 500;
    }
  }
}
</style>
