<template>
  <div  class="change-view">
    <a @click="view = 'grid'" class="change-view__item" :class="{'change-view__item--active': view === 'grid'}">
      <c-icon icon="fa-th-large" />
    </a>
    <a @click="view = 'list'" class="change-view__item" :class="{'change-view__item--active': view === 'list'}">
      <c-icon icon="fa-list-ul" />
    </a>
  </div>
</template>
<script lang="ts" setup>
defineModel<'grid' | 'list'>();
</script>
<style lang="scss">
.change-view {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  &__item {
    padding: .5em 1em;

    cursor: pointer;
    display: inline-block;
    position: relative;
    z-index: 1;
    transition: all .4s;
    border: 1px solid var(--input-border-color);
    &:hover {
      background: var(--lighten-1);
    }
    &--active {
      background: var(--lighten-2);
    }
  }
}

</style>
