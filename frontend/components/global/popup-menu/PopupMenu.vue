<template>
  <div class="popup-menu" v-click-outside="onClickOutside" :class="menuClasses">
    <div class="popup-menu__inner">
      <slot></slot>
    </div>
  </div>
</template>
<script>
import clickOutside from 'vue-click-outside';
export default {
  computed: {
    menuClasses() {
      let classes = [
        (this.manual && this.visible || this.val) ? 'popup-menu--visible' : '',
        this.manual ? 'popup-menu--manual' : ''
      ];
      if (this.position) {
        let parts = this.position.split('-');
        if (parts.length > 1) {
          parts.forEach(part => {
            classes.push(`popup-menu--${part}`);
          })
        } else {
          classes.push(`popup-menu--${this.position}`);
        }
      }
      return classes;
    }
  },
  directives: {
    clickOutside
  },
  props: {
    value: Boolean,
    manual: Boolean,
    position: String,
    visible: Boolean,
    activateOnParentClick: Boolean,
  },
  mounted() {
    if (this.activateOnParentClick) {
      this.$parent?.$el?.addEventListener('click', this.showMenuOnParentClick);
    }
  },
  beforeDestroy() {
    if (this.activateOnParentClick) {
      this.$parent?.$el?.removeEventListener('click', this.showMenuOnParentClick);
    }
  },
  watch: {
    value(newVal) {
      this.val = newVal;
      this.lastChangeTime = new Date().getTime();
    }
  },
  data() {
    return {
      val: this.value,
      lastChangeTime: 0
    }
  },
  methods: {
    showMenuOnParentClick() {
      this.$emit('open');
      this.val = true;
    },
    onClickOutside(e) {
      if (this.activateOnParentClick && (e.target === this.$parent?.$el || this.$parent?.$el?.contains(e.target))) {
        return;
      }
      if (new Date().getTime() - this.lastChangeTime > 500) {
        this.val = false;
        this.$emit('input', false);
      }
    }
  },
}
</script>
<style lang="scss">
  .popup-menu {
    position: absolute;
    display: none;
    text-align: center;
    left: 0;
    z-index: 10000000;
    transition: opacity .2s;
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

