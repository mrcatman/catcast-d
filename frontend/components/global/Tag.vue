<template>
  <component :is="to ? 'nuxt-link' : 'span'" :to="to" class="tag" :class="[color ? `tag--${color}` : '']">
    <slot></slot>
    <span v-if="count" class="tag__count">{{count}}</span>
  </component>
</template>
<style lang="scss">
.tag {
  font-size: .875em;
  margin: .25em 0 0 .5em;
  padding: .25em .5em;
  border-radius: .25em;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  white-space: nowrap;
  background: var(--active-color);
  &--transparent {
    background: var(--darken-5);
  }
  &--green{
    background: var(--green);
  }
  &--red{
    background: var(--red);
  }
  &--white{
    color: var(--blue);
    background: var(--white);
  }
  & i {
    margin-right: .5em;
  }
  &__count {
    margin-left: .5em;
    font-weight: 500;
    font-size: .75em;
  }
}
a.tag:hover {
  filter: brightness(1.1);
}
.buttons-bright a.tag :hover {
  filter: brightness(.9);
}
</style>
<script>
export default {
  props: {
    color: String,
    to: String,
    count: Number
  }
}
</script>
