<template>
  <div class="statistics-icons">
    <component v-if="item.value !== undefined && item.value !== null" :is="item.link ? 'nuxt-link' : 'span'" :to="item.link" class="statistics-icons__item" :class="{'statistics-icons__item--with-margin': item.margin}" v-for="(item, $index) in data" :key="$index">
      <c-icon :icon="item.icon" />
      <c-tooltip v-if="item.tooltip" position="bottom-left">{{item.tooltip}}</c-tooltip>
      <span class="statistics-icons__item__text">{{item.value}}</span>
    </component>
  </div>
</template>
<style lang="scss">
.statistics-icons {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  font-size: .75em;
  &__item {
    display: flex;
    align-items: center;
    margin-right: .75em;
    text-decoration: none;
    &--with-margin {
      margin-right: 1.5em;
    }
    i {
      font-size: inherit;
    }
    &__text {
      margin-left: .5em;
    }
  }
  &:last-child {
    margin-right: 0;
  }
}
a.statistics-icons__item:hover {
  color: var(--lighten-5);
}
</style>
<script>
export default {
  props: {
    data: {
      type: Array,
      required: true
    }
  }
}
</script>
