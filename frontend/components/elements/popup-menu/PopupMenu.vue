<template>
  <div class="popup-menu" v-click-outside="onClickOutside"  :class="{'popup-menu-hover': visible || val, 'popup-menu-manual': manual}">
    <div class="popup-menu__triangle"></div>
    <div class="popup-menu__inner">
      <slot></slot>
    </div>
  </div>
</template>
<script>
import clickOutside from 'vue-click-outside';
export default {
    directives: {
      clickOutside
    },
    props: {
      value: {
        type: [Boolean],
        required: false,
      },
      manual: {
        type: [Boolean],
        required: false,
      },
      visible: {
        type: [Boolean],
        required: false,
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
      onClickOutside(e) {
        if (new Date().getTime() - this.lastChangeTime > 500) {
          this.val = false;
          this.$emit('input', false);
        }
      }
    },
    mounted() {

    }
  }
</script>
<style lang="scss">
  .popup-menu{
    position: absolute;
    display: none;
    text-align: center;
    left: 0;
    z-index: 10000000;
    &--full-width {
      width: 100%;
    }
    &-bottom .popup-menu__triangle {
      border-color: transparent transparent var(--menu-color) transparent;
    }
    &-top .popup-menu__triangle {
      border-color: var(--menu-color) transparent transparent transparent;
    }
    &-left .popup-menu__triangle {
      border-color: transparent transparent transparent var(--menu-color);
    }
    &-right .popup-menu__triangle {
      border-color: transparent var(--menu-color) transparent transparent;
    }
    &__inner {
      font-size: .875rem;
      background: var(--menu-color);
    }
    &__item{
      color: var(--text-color);
    }
    &-container{
      display: inline-block;
      position:relative;
    }
    &__triangle {
      width: 0;
      height: 0;
      border-style: solid;
      margin: 0 auto;

    }
    &-bottom {
      top: 2em;
      flex-direction:column;
    }
    &-bottom > &__triangle {
      border-width: 0 10px 10px 10px;
    }
    &-top{
      bottom: 2em;
      flex-direction: column-reverse;
    }
    &-top > &__triangle {
      position: relative;
      z-index: 1;
      border-width: 10px 10px 0 10px;
    }
    &__header{
      white-space:nowrap;
      font-weight:bold;
      padding:5px 12px;
    }
    &-left {
      left: auto;
      right: calc(100% + 10px);
      top: 0;
      flex-direction: row-reverse;
    }
    &-left > &__triangle {
      border-width: 10px 0 10px 10px;
      margin: auto 0;
    }

    &-right {
      right: auto;
      left: 100%;
      top: 0;
      flex-direction: row
    }
    &-right > &__triangle {
      border-width: 10px 10px 10px 0;
      margin: auto 0;
    }

    &-top-left{
      right: 0;
      left: auto;
    }
    &-top-left > &__triangle{
      margin: auto 8px auto auto;
    }


    &-top-right{
      left: 0;
      right: auto;
    }
    &-top-right > &__triangle{
      margin: auto auto auto 8px;
    }

    &-bottom-left{
      right: 0;
      left: auto;
    }
    &-bottom-left > &__triangle{
      margin: auto 8px auto auto;
    }

    &-bottom-right{
      top: auto;
      bottom: 0;
    }
    &-bottom-right > &__triangle{
      margin: auto 0 8px 0;
    }



    &-left-top {
      top: auto;
      bottom: 0;
    }

    &-left-top > &__triangle{
      margin: auto auto 3px auto;
    }

    &__item {
      position: relative;
      cursor: pointer;
      padding: .5em 1em;
      text-decoration: none;
      display: flex;
      align-items: center;
      white-space: nowrap;
      &--selected {
        background: var(--active-color);
        &:hover {
          background: var(--active-color)!important;
          box-shadow: 0 0 .5em var(--active-color);
        }
      }
      &__number {
        background: var(--red);
        font-weight: 600;
        padding: 0 .25em;
        margin: 0 0 0 .5em;
      }
      &__avatar{
        width:1em;
        height:1em;
        margin:0 5px;
        background-size:contain;
        background-position:center center;
      }
      &:hover{
        background:rgba(255,255,255,0.05);
      }
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
  *:hover > .popup-menu:not(.popup-menu-manual), .popup-menu:not(.popup-menu-manual):hover, .popup-menu-hover,.popup-menu__item:hover > .popup-menu-container > .popup-menu{
    display: flex;
    opacity: 0;
    animation: popupMenuShow 0.25s forwards;
  }
  *:hover > .popup-menu-top, .popup-menu-top:hover,.popup-menu-top.popup-menu-hover {
    animation: popupMenuTopShow 0.25s forwards;
  }
  @keyframes popupMenuShow {
    0% {
      opacity:0;
      transform:translateY(-1em);
    }
    100% {
      opacity:1;
      transform:translateY(0);
    }
  }
  @keyframes popupMenuTopShow {
    0% {
      opacity: 0;
      transform: translateY(1em);
    }
    100% {
      opacity:1;
      transform: translateY(0);
    }
  }
</style>

