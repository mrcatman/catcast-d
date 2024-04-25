<template>
  <component :is="to ? 'nuxt-link' : 'div'" :to="to" @click="onClick" class="popup-menu__item" :class="{'popup-menu__item--selected': selected, 'popup-menu__item--with-delimiter': withDelimiter}">
    <div v-if="picture" class="popup-menu__item__picture" :style="{backgroundImage: `url(${picture})`}"></div>
    <div v-if="icon" class="popup-menu__item__icon-container">
      <c-icon class="popup-menu__item__icon" :icon="icon" />
    </div>
    <slot></slot>
  </component>
</template>
<script>
  export default {
    props: {
      selected: Boolean,
      to: String,
      icon: String,
      picture: String,
      withDelimiter: Boolean,
    },
    methods: {
      onClick(e) {
        this.$emit('click', e);
      }
    }
  }
</script>
<style lang="scss" scoped>
.popup-menu {
  &__item {
    font-weight: 400;
    position: relative;
    cursor: pointer;
    font-size: 1rem;
    padding: .625em .75em;
    text-decoration: none;
    display: flex;
    align-items: center;
    white-space: nowrap;
    color: var(--text-color) !important;
    &:hover {
      background: var(--lighten-1);
    }
    &--selected {
      background: var(--active-color);

      &:hover {
        background: var(--active-color) !important;
        box-shadow: 0 0 .5em var(--active-color);
      }
    }
    &--with-delimiter {
      border-top: 1px solid var(--input-border-color);
    }
    &__icon-container {
      width: 1em;
      margin-right: 1em;
    }
    &__picture {
      width: 1em;
      height: 1em;
      background-size: cover;
      margin-right: 1em;
    }
    &__number {
      background: var(--red);
      font-weight: 600;
      padding: 0 .25em;
      margin: 0 0 0 .5em;
    }

    &__avatar {
      width: 1em;
      height: 1em;
      margin: 0 5px;
      background-size: contain;
      background-position: center center;
    }


  }
}
</style>
