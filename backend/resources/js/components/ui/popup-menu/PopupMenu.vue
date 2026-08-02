<template>
  <div class="popup-menu" ref="menu" v-click-outside="onClickOutside" :class="menuClasses">
    <div class="popup-menu__inner">
      <slot></slot>
    </div>
  </div>
</template>
<script lang="ts" setup>
import vClickOutside from 'click-outside-vue3'

const props = defineProps<{
  position?: String,
  manual?: boolean,
  visible?: boolean,
  activateOnParentClick?: boolean,
}>();

const opened = defineModel<boolean>(false);

const emit = defineEmits<{
  (e: 'open'): void
}>();

const menuRef = useTemplateRef('menu');
let parentEl: HTMLElement;

const menuClasses = computed(() => { // todo: maybe use tooltip code
  let classes = [
    (props.manual && props.visible || opened.value) ? 'popup-menu--visible' : '',
    props.manual ? 'popup-menu--manual' : ''
  ];
  if (props.position) {
    let parts = props.position.split('-');
    if (parts.length > 1) {
      parts.forEach(part => {
        classes.push(`popup-menu--${part}`);
      })
    } else {
      classes.push(`popup-menu--${props.position}`);
    }
  }
  return classes;
})

const lastChangeTime = 0;

const open = () => {
  emit('open');
  opened.value = true;
}

const onClickOutside = (e: ClickEvent) => {
  if (props.activateOnParentClick && (e.target === parentEl || parentEl?.contains(e.target))) {
    return;
  }
  if (new Date().getTime() - lastChangeTime > 500) {
    opened.value = false;
  }
}

onMounted(() => {
  parentEl = menuRef.value.parentElement;
  if (props.activateOnParentClick) {
    console.log(parentEl);
    parentEl?.addEventListener('click', open);
  }
})

onBeforeUnmount(() => {
  if (props.activateOnParentClick) {
    parentEl?.removeEventListener('click', open);
  }
})

</script>
<style lang="scss">
  .popup-menu {
    position: absolute;
    display: none;
    text-align: center;
    left: 0;
    z-index: 10000000;
    transition: opacity .2s;
    font-size: 1rem;
    &--full-width {
      width: 100%;
    }
    &__inner {
      background: var(--menu-color);
      border-radius: var(--border-radius); // todo: set in other components
    }

    &--bottom {
      top: 100%;
      flex-direction: column;
    }


    &--top {
      bottom: 100%;
      flex-direction: column-reverse;
    }

    &__header {
      white-space: nowrap;
      font-weight: bold;
    }

    &--left {
      right: 0;
      left: auto;
    }
    &--right {
      left: 0;
      right: auto;
    }


    &--bottom-left &__item {
      justify-content: flex-end;
    }

    & &--top {
      bottom: 0;
    }
    & &--bottom {
      top: 0;
    }

    & &--left {
      right: 100%;
    }
    & &--right {
      left: 100%;
    }


    &--big &__inner {
      font-size: 1rem;
    }

    &__texts {
      display: flex;
      align-items: center;
      flex: 1;
    }

    &__icon {
      margin: 0 .5em 0 0;
      width: 1.25em;
    }

    &__buttons {
      display: flex;
      flex: 1;
      justify-content: flex-end;
      margin: 0 0 0 1em;
    }

    &__button {
      font-size: .875em;
      margin: 0 0 0 .75em;
      opacity: .5;

      &:hover {
        opacity: .85;
      }
    }
  }

  *:hover > .popup-menu:not(.popup-menu--manual), .popup-menu:not(.popup-menu--manual):hover, .popup-menu--visible,.popup-menu__item:hover > .popup-menu-container > .popup-menu{
    display: flex;
    opacity: 1;
  /*  animation: popupMenuShowFromBottom .2s forwards; */
  }
  *:hover > .popup-menu--top, .popup-menu--top:hover,.popup-menu--top.popup-menu--visible {
  /*  animation: popupMenuShowFromTop .2s forwards; */
  }
@keyframes popupMenuShowFromBottom {
  0% {
    opacity: 0;
    transform: translateY(-1em);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}
@keyframes popupMenuShowFromTop {
  0% {
    opacity: 0;
    transform: translateY(1em);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

