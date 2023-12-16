<template>
  <div class="box" :class="{'box--no-padding': noPadding, 'box--with-header': $slots.title || $slots.title_buttons, 'box--with-footer': $slots.footer, 'box--empty': !$slots.main}">
    <component ref="header" :is="link ? 'nuxt-link' : 'div'" :to="link" class="box__header" v-if="$slots.title || $slots.title_buttons">
      <div class="box__header__title" v-if="$slots.title">
        <slot name="title"></slot>
      </div>
      <div class="box__header__buttons" v-if="$slots.title_buttons">
        <div class="buttons-row">
          <slot name="title_buttons"></slot>
        </div>
      </div>
    </component>
    <div ref="main" class="box__inner">
      <slot name="main"></slot>
    </div>
    <div ref="footer" class="box__footer" >
      <slot name="footer"></slot>
    </div>
  </div>
</template>
<style lang="scss">

.box {
  padding: 1em;
  display: block;
  text-decoration: none;
  position: relative;
  color: var(--text-color);
  background: var(--box-color);

  &__header {
    text-decoration: none;
    background: var(--box-header-color);
    font-weight: 600;
    padding: .5em 1em;
    display: flex;
    justify-content: space-between;
    align-items: center;
    &:empty {
      display: none;
    }
    &--dark {
      background: var(--box-footer-color);
    }
    &__title {
      font-weight: 500;
      font-size: 1.125em;
      display: flex;
      align-items: center;
      margin-right: 1em;
    }
    &__count {
      font-weight: bold;
    }
    &__icon-container {
      line-height: 0;
      cursor: pointer;
      font-size: 1.25em;
    }

    &__buttons {
      position: relative;
      display: flex;
    }

  }

  &__inner {
    &:empty {
      display: none;
    }
  }

  &__footer {
    padding: 1em;
    background: var(--box-footer-color);
    display: flex;
    &:empty {
      display: none;
    }
    &__buttons {
      margin-left: .5em;
    }
  }

  &--disabled {
    opacity: .25;
  }

  &--with-header, &--with-footer {
    padding: 0;
  }

  &--with-header > &__inner, &--with-footer > &__inner {
    padding: 1em;
  }

  &--no-padding {
    padding: 0;
  }
  &--no-padding > &__inner {
    padding: 0;
  }

}
.box + .box, .form + .box {
  margin-top: 1em;
}
.theme-modern .box {
  box-shadow: 0 0.5em 1em -0.75em var(--darken-4);
}
.theme-flat .box {
  border: 1px solid var(--input-border-color);
  border-radius: .25em;
  &__header {
    padding: 1em;
    border-bottom: 1px solid var(--input-border-color);
    &__title {
      font-size: 1.125em;
    }
  }
  &--empty &__header {
    border-bottom: none;
  }
  &__footer {
    background: none;
  }
}
</style>
<script>
export default {
	props: {
    link: String,
    noPadding: Boolean,
	}
}
</script>
