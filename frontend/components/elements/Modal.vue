<template>
  <portal to="modals">
  <div class="modal" :class="{'modal--inline': inline, 'modal--no-padding': noPadding}" v-if="value">
    <div class="modal__background" @click="closeModal"></div>
    <div class="modal__box" :class="{'modal__box--autosize':autoSize}">
      <a v-show="showCloseButton" @click="closeModal" class="modal__close">
        <m-button flat rounded icon="close"></m-button>
      </a>
      <div class="modal__title" v-if="title">{{title}}</div>
      <div class="modal__content">
        <slot></slot>
      </div>
      <slot class="modal__buttons" name="buttons"></slot>
    </div>
  </div>
</portal>
</template>
<style lang="scss">
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 10000000;
  display: flex;
  align-items: center;
  justify-content: center;

  &--inline {
    position: absolute;
  }

  &__background {
    background: rgba(0, 0, 0, 0.35);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 100000;
  }

  &--inline &__background {
    position: absolute;
  }

  &__input-container {

  }

  &__files-list__item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-align: left;
    padding: .5em 1em;
    background: rgba(255, 255, 255, 0.05);

    &__title {
      margin: 0 1.25em 0 0;
    }
  }

  &__title {
    font-weight: 500;
    background: linear-gradient(-90deg,var(--box-footer-color),var(--active-color));
    padding: .75em 1em;
  }

  &__box {
    position: relative;
    z-index: 100000000;
    min-width: 640px;
    max-width: 80vw;

    background: var(--box-color);
    color: var(--text-color);
    min-height: .625em;
    &--autosize {
      min-width: 0;
      max-height: 100%;
    }
  }

  &__buttons {

    padding: 1em;
    background: var(--box-footer-color);

    &:empty {
      display: none;
    }
  }

  &__content {
    padding: 1em;
    height: 100%;
    max-height: 70vh;
    overflow-y: auto;
  }

  &--no-padding &__content {
    padding: 0;
  }

  &__list-container {
    height: 100%;
    max-height: 70vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }

  &__text {
    padding: 1em 0;
  }

  &__close {
    position: absolute;
    top: .25em;
    right: .5em;
    z-index: 10000;

    .btn {
      color: var(--active-color) !important;
    }
  }

  &__users-list {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    max-width: 75vw;
    text-decoration: none;
    color: var(--text-color) !important;
    text-align: center;

    &__item {
      width: 10em;
      overflow: hidden;
      transition: all .35s;
      text-decoration: none;

      &:hover {
        background: rgba(255, 255, 255, 0.1);
      }

      &__username {
        font-size: .875em;
      }

      &__avatar {
        margin: 0 0 1em;
        width: 100%;
        padding-top: 50%;
        background-size: contain;
        background-position: center center;
        background-repeat: no-repeat;
      }

      &__inner {
        margin: 1em;
      }
    }
  }

  @media screen and (max-width: 768px) {
    & {
      &__box {
        width: 100%;
        min-width: 0;
      }

      .buttons-list {
        flex-direction: row;
      }
    }
  }
}
</style>
<script lang="ts">
  import Vue from 'vue'
  export default Vue.extend({
    props: {
      inline: {
        type: Boolean,
        required: false,
      },
      showCloseButton: {
        type: Boolean,
        required: false,
        default: true,
      },
      autoSize: {
        type: Boolean,
        required: false,
      },
      title: {
        type: String,
        required: false,
        default: '',
      },
      value: {
        type: Boolean,
        required: false,
      },
      noPadding: {
        type: Boolean,
        required: false,
      }
    },
    methods: {
      closeModal(): void {
        this.$emit('input', false);
      }
    }
  });
</script>
