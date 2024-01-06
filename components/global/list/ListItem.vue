<template>
<div class="list-item" :class="parentClasses">
  <component :is="to ? 'router-link' : 'div'" :to="to" class="list-item__left">
    <div v-if="picture || icon || showPicturePlaceholder" class="list-item__picture-container" :class="{'list-item__picture-container--square': pictureSquare}">
      <div class="list-item__picture" :style="pictureStyle">
        <c-icon v-if="icon" :icon="icon" class="list-item__icon" />
        <div v-if="length" class="list-item__length">{{lengthFormatted}}</div>
      </div>
    </div>
    <div class="list-item__captions">
      <slot name="captions"></slot>
    </div>
  </component>
  <div class="list-item__right">
    <div class="list-item__buttons">
      <slot name="buttons"></slot>
    </div>
  </div>
</div>
</template>
<style lang="scss">
.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  text-decoration: none;
  padding: .75em;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  transition: all .25s;
  position: relative;
  &--not-confirmed {
    opacity: .75;
  }
  &:hover {
    background: var(--lighten-1);
  }
  &__date-container {
    text-align: right;
  }
  &__date {
    text-align: right;
    white-space: nowrap;
    font-weight: 500;
    margin: 0 0 .5em;
  }
  &__left {
    height: 100%;
    display: flex;
    align-items: center;
    text-decoration: none;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
  }
  &__right {
    height: 100%;
    display: flex;
    align-items: center;
  }

  &--not-link {
    cursor: default;
    &:hover {
      background: none;
    }
  }
  &--selected {
    background: var(--lighten-2);
    &:hover {
      background:var(--lighten-3);
    }
  }
  &--selected-with-area {
    background: rgba(255, 255, 255, 0.05);
    &:hover {
      background: rgba(255, 255, 255, 0.175);
    }
  }
  &__captions {
    overflow: hidden;
    flex: 1;
  }
  &__title {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 1.125em;
    margin-bottom: .25em;
    font-weight: 600;
    &__sub {
      margin: -.75em 0 .25em;
      display: block;
      font-size: .75em;
    }
  }
  &__under-title {
    margin-top: .5em;
    display: flex;
    align-items: center;
    line-height: 1.4;
    &--small {
      font-size: .875em;
    }
    &--short {
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      line-clamp: 3;
      -webkit-box-orient: vertical;
    }
  }

  &__buttons {
    display: flex;
    margin-left: 1em;
    .button {
      margin: 0 .5em;
    }
  }

  &__picture-container {
    min-width: 6.5em;
    width: 6.5em;
    height: 3.5em;
    position: relative;
    margin-right: 1em;
    &--square {
      min-width: 3.5em;
      width: 3.5em;
    }
  }
  &__picture {
    background-color: var(--darken-4);
    background-position: center;
    background-size: contain;
    background-repeat: no-repeat;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  &__length {
    font-size: .875em;
    position: absolute;
    bottom: .5em;
    left: .5em;
    padding: .25em;
    background: var(--darken-5);
  }

  &__icon {
    font-size: 2em!important;
  }
  .tag {
    margin: 0 .5em 0 0;
  }
  &--without-picture &__captions {
    margin: 0;
  }

  &__number {
    background: var(--green);
    padding: .5em 1em;
    border-radius: .5em;
  }


  .view-grid & {
    flex-direction: column;
    padding: 1em;
    &__left {
      flex-direction: column;
      width: 100%;
    }
    &__picture-container {
      width: 100%;
      height: unset;
      padding-top: 60%;
    }
    &__captions {
      width: 100%;
      margin-left: 0!important;
      padding: 1em 0;
    }
    &__right {
      font-size: .8125em;
      width: 100%;
      height: unset;
    }
    &__buttons {
      position: absolute;
      top: 1.5em;
      right: .5em;
      margin: 0;
       .button {
        margin-left: 0;
        margin-right: 1em;
      }
    }
  }
}
</style>
<script>
import {mapGetters} from "vuex";

export default {
  computed: {
    ...mapGetters('config', ['siteLogoSquare']),
    parentClasses() {
      return {
        'list-item--not-confirmed': this.notConfirmed,
      }
    },
    pictureStyle() {
      if (this.picture) {
        return {
          backgroundImage: `url(${this.picture})`
        }
      } else if (!this.icon) {
        return {
          backgroundImage: `url(${this.siteLogoSquare})`,
          backgroundSize: '50%',
          opacity: .5
        }
      }
    },
    lengthFormatted() {
      return new Date(this.length * 1000).toISOString().substring(11, 19)
    },
  },
  props: {
    to: [String, Object],
    picture: String,
    pictureSquare: Boolean,
    icon: String,
    length: Number,
    notConfirmed: Boolean,
    showPicturePlaceholder: Boolean,
  }
}
</script>
