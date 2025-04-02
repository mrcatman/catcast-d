<template>
<component
  @click="handleClick"
  :is="buttonTag"
  :to="to"
  :target="buttonTarget"
  class="button" :class="buttonClasses">
  <div class="button__background" :style="buttonStyle"></div>
  <div ref="content" class="button__content" :style="buttonContentStyle">
    <c-icon :icon="icon" class="button__icon button__icon--left" :class="{'button__icon--only': iconOnly}" v-if="icon" />
    <slot name="tooltip"></slot>
    <span class="button__content__text">
      <slot></slot>
    </span>
    <span v-if="loading" class="button__loading-icon">
      <c-preloader />
    </span>
    <span class="button__count" v-if="count !== undefined">{{count}}</span>
  </div>
</component>
</template>
<style lang="scss">
.button {
  cursor: pointer;

  transition: all .2s;
  height: 2.75em;
  display: inline-flex;
  align-items: center;
  text-decoration: none;
  position: relative;
  font: inherit;
  color: inherit;
  border: none;
  font-size: .875em;
  font-weight: 400;

  &--big {
    display: flex;
    width: 100%;
  }

  &__background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--active-color);
    border-radius: var(--border-radius);
    z-index: 0;
  }
  &:hover &__background {
    filter: brightness(1.1);
  }

  &__content {
    box-sizing: border-box;
    width: 100%;
    height: 100%;
    line-height: 1;
    padding: 1.875em 1.25em;
    display: flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    position: relative;
    z-index: 1;
    &__text {
      position: relative;
    }
  }

  &__icon {
    line-height: 0;
    font-size: 1.5em!important;
    &.fa {
      font-size: 1.25em!important;
    }
    &--left {
      margin-right: .5em;
    }
    &--right {
      margin-right: .5em;
    }
    &--only {
      margin-left: 0;
      margin-right: 0;
    }
  }

  &--disabled {
    cursor: not-allowed;
    opacity: .5;
  }

  &--rounded {
    width: 3em;
    height: 3em;
    padding: 0;
    line-height: 1.5em;
  }
  &--rounded &__background {
    border-radius: 50% !important;
  }

  &--rounded &__icon {
    margin: 0;
  }
  &--rounded &__content {
    padding: .5em;
  }
  &__loading-icon {
    margin-left: .75em;
    line-height: 0;
    .preloader-container {
      padding: 0;
      height: 1.75em;
    }
  }
  &__count {
    margin-left: .75em;
    font-weight: bold;
  }

  &--green {
    background: var(--green);
  }

  &--red {
    background: var(--red);
  }

  .theme-default & {
    font-weight: 500;
    text-transform: uppercase;
    box-shadow: 0 5px 10px -3px rgba(0, 0, 0, 0.5);
  }

  .theme-modern & {
    border-radius: var(--border-radius);
    box-shadow: 0 .5em 1.25em -.25em;
    &:hover {
      box-shadow: 0 .5em 1.25em .25em;
    }
  }

  .theme-flat & {
    border-radius: var(--border-radius);
  }

  .theme-modern &--disabled, .theme-modern &--disabled:hover {
    box-shadow: none !important;
  }
  .theme-light & {
    text-transform: uppercase;
    border-radius: 5px;
    font-weight: 400;
    letter-spacing: .05em;
    box-shadow: 0 .5em 3.5em -.5em;

    &__content {
      padding: .5em 1em;
      background: linear-gradient(90deg, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0));
    }

    &--flat {
      background: var(--lighten-2);
    }
  }
  &--icon-only &__content{
    margin: 0;
  }

  &--narrow &__content {
    padding: 0;
  }

  &--transparent &__background {
    background: none;
    box-shadow: none !important;
  }
  &--transparent &__content {
    color: rgba(255, 255, 255, .875) !important;
  }
  &--transparent:hover &__content {
    color: rgba(255, 255, 255, .5) !important;
  }

  &--transparent#{&}--disabled {
    background: none!important;
    &:hover &__content {
      opacity: 1;
    }
  }


  &--flat &__background {
    background: var(--lighten-1);
    box-shadow: none!important;
    &:hover {
      background: var(--lighten-2);
    }
  }
}
.buttons-bright .button:hover .button__background {
  filter: brightness(.9);
}
.bright .button-flat .button__background {
  background: rgba(0, 0, 0, 0.25) !important;
}
</style>
<script lang="ts" setup>
const predefinedColors = ['green', 'red'];
export interface ButtonProps {
  icon?: string;
  iconOnly?: boolean;
  color?: string;

  count?: number;

  big?: boolean;
  narrow?: boolean;
  disabled?: boolean;
  primary?: boolean;
  flat?: boolean;
  transparent?: boolean;
  rounded?: boolean;
  loading?: boolean;

  to?: string;
  target?: string;
  tag?: 'a' | 'button';
}

const props = defineProps<ButtonProps>();

const emit = defineEmits<{
  (e: 'click'): void
}>()


const contentRef = useTemplateRef('content')

const textColor = ref<string>();
const loaded = ref<boolean>(false);

onMounted(() => {
  loaded.value = true;
  textColor.value = window.getComputedStyle(contentRef.value).getPropertyValue('color');
})

const buttonClasses = computed(() => {
  return {
    'button--big': props.big,
    'button--primary': props.primary,
    'button--flat': props.flat,
    'button--transparent': props.transparent,
    'button--rounded': props.rounded,
    'button--loading': props.loading,
    'button--disabled': props.disabled,
    'button--icon-only': props.iconOnly,
    'button--narrow': props.narrow
  }
})


const buttonStyle = computed(() => {
  if (!loaded.value) {
    return {
      boxShadow: 'none!important'
    }
  }
  if (props.disabled) {
    return {
      opacity: .5,
      backgroundColor: !props.transparent ? 'var(--lighten-2)!important' : '',
      color: 'var(--text-color)!important'
    }
  }
  if (props.flat || props.transparent) {
    return {};
  }

  const color = predefinedColors.includes(props.color) ? `var(--${props.color})` : props.color;
  return {
    backgroundColor: color ?? 'var(--active-color)',
    color: color ?? 'var(--active-color)',
  }
})

const buttonTarget = computed(() => {
  if (props.target) {
    return props.target;
  }
  if (props.to && props.to[0] !== "/") {
    return "_blank";
  }
  return null;
})

const buttonTag = computed(() => {
  if (props.tag) {
    return props.tag;
  }
  if (props.to && props.to[0] === "/") {
    return resolveComponent('NuxtLink');
  }
  return "a";
});

const buttonContentStyle = computed(() => {
  return {
    color: props.color
  }
})

const handleClick = () => {
  if (!props.disabled && !props.loading) {
    emit('click');
  }
}

</script>
