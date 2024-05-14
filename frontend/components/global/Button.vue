<template>
<component
  @click="handleClick"
  :is="buttonComponentName"
  :to="buttonLink"
  :target="buttonTarget"
  class="button" :class="buttonClasses">
  <div class="button__background" :style="buttonStyle"></div>
  <div ref="content" class="button__content" :style="buttonContentStyle">
    <c-icon :icon="icon" class="button__icon button__icon--left" :class="{'button__icon--only': iconOnly}" v-if="icon && iconPosition !== 'right'" />
    <slot name="tooltip"></slot>
    <span class="button__content__text">
      <slot></slot>
    </span>
    <c-icon :icon="icon"  class="button__icon button__icon--right" v-if="icon && iconPosition === 'right'" />
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
  font-size: .875em;
  transition: all .2s;
  height: 2.75em;
  display: inline-flex;
  align-items: center;
  text-decoration: none;
  position: relative;
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
<script>
const predefinedColors = ['green', 'red'];
export default {
  props: {
    icon: String,
    iconPosition: String,
    iconOnly: Boolean,
    narrow: Boolean,
    count: Number,
    disabled: Boolean,
    primary: Boolean,
    flat: Boolean,
    transparent: Boolean,
    rounded: Boolean,
    to: String,
    target: String,
    loading: Boolean,
    big: Boolean,
    color: String,
  },
  data() {
    return {
      loaded: false,
      textColor: '',
    }
  },
  mounted() {
    //this.textColor = window.getComputedStyle(this.$refs.content).getPropertyValue('color');
    this.loaded = true;
  },
  computed: {
    buttonStyle() {
      if (!this.loaded) {
        return {
          boxShadow: 'none!important'
        }
      }
      if (this.disabled) {
        return {
          opacity: .5,
          backgroundColor: !this.transparent ? 'var(--lighten-2)!important' : '',
          color: 'var(--text-color)!important'
        }
      }
      return !this.flat && !this.transparent ? {
        backgroundColor: this.color ? (predefinedColors.indexOf(this.color) !== -1 ? 'var(--' + this.color + ')' : this.color) : 'var(--active-color)',
        color: this.color ? (predefinedColors.indexOf(this.color) !== -1 ? 'var(--' + this.color + ')' : this.color) : 'var(--active-color)',
      } : {}
    },
    buttonContentStyle() {
      return {
        color: this.textColor ? this.textColor : ''
      }
    },
    buttonClasses() {
      return {
        'button--big': this.big,
        'button--primary': this.primary,
        'button--flat': this.flat,
        'button--transparent': this.transparent,
        'button--rounded': this.rounded,
        'button--loading': this.loading,
        'button--disabled': this.disabled,
        'button--icon-only': this.iconOnly,
        'button--narrow': this.narrow
      }
    },
    buttonLink() {
      return this.to;
    },
    buttonTarget() {
      if (this.target) {
        return this.target;
      }
      if (this.to) {
        if (this.to[0] !== "/") {
          return "_blank";
        }
      }
      return "";
    },
    buttonComponentName() {
      if (this.to) {
        if (this.to[0] === "/") {
          return "nuxt-link";
        }
      }
      return "a";
    }
  },
  methods: {
    handleClick(e) {
      if (!this.disabled && !this.loading) {
        this.$emit('click', e);
      }
    }
  }
}
</script>
