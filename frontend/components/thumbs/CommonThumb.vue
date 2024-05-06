<template>
  <component :is="link ? 'nuxt-link' : 'div'" :to="link" class="thumb" :class="{'thumb--clickable': !!link, 'thumb--highlighted': highlighted}">
    <div class="thumb__inner">
      <div class="thumb__picture-container" >
        <div class="thumb__picture thumb__picture--default" :style="{backgroundImage: `url(${siteLogoSquare}`}"></div>
        <div class="thumb__picture" :style="{backgroundImage: picture ? `url(${picture}` : ''}">
          <slot name="inside_picture"></slot>
        </div>
      </div>
      <div class="thumb__bottom">
        <div class="thumb__texts-container">
          <div class="thumb__logo" v-if="logo" :style="{backgroundImage: `url(${logo}`}"></div>
          <div class="thumb__texts">
            <slot name="texts"></slot>
          </div>
        </div>
        <div class="thumb__list-texts-container">
          <slot name="list_texts"></slot>
        </div>
      </div>

    </div>
  </component>
</template>
<style lang="scss">
.thumb {
  height: 100%;
  text-decoration: none;
  display: block;
  &--highlighted {
    background: var(--lighten-2);
  }
  &__inner {
    width: 100%;
    height: 100%;
    padding: 0.5em;
    box-sizing: border-box;
  }
  &__tags {
    display: none;
  }
  &__picture-container {
    position: relative;
    padding-top: 60%;
    background: rgba(0, 0, 0, .25);
    box-shadow: 0 0 0 var(--active-color);
    transition: all .25s;
    border: 1px solid var(--input-border-color);
  }
  &--clickable:hover &__picture-container {
    box-shadow: 0 0 .5em .25em var(--active-color);
  }
  &__picture {
    position: absolute;
    z-index: 1;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: contain;
    background-position: center center;
    background-repeat: no-repeat;
    transition: all .25s;
    &--default {
      left: 2em;
      top: 2em;
      width: calc(100% - 4em);
      height: calc(100% - 4em);
      opacity: .5;
      z-index: 0;
    }
    &--thumbnail{
      background-size: cover;
    }
  }
  &__bottom {
    margin-top: .5em;
    display: flex;
    align-items: center;
  }
  &__logo {
    width: 2.5em;
    height: 2.5em;
    background-position: center;
    background-size: cover;
    margin-right: .5em;
  }
  &__texts-container {
    max-width: 100%;
    display: flex;
    flex: 1;
  }
  &__texts {
    max-width: 100%;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  &__title {
    line-height: 1.4;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
  }
  &__small-title {
    font-size: .875em;
    font-weight: 300;
  }
  &__list-texts-container {
    display: none;
  }
  &__statistics-icons {
    margin-top: .5em;
  }
}

.bright .thumb--highlighted {
  background: var(--darken-2);
}

.view-list {
  .thumb {
    height: unset;
    &__inner {
      display: flex;
      margin-bottom: 1em;
      font-size: .875em;
    }
    &__picture-container {
      width: 16em;
      height: 9em;
      padding: 0;
    }
    &__bottom {
      display: block;
      width: calc(100% - 16em);
      padding-left: 1em;
    }
    &__bottom {
      margin-top: 0;
    }
    &__list-texts-container {
      display: block;
    }
    &__description {
      font-size: .875em;
      line-height: 1.6;
      margin-top: 1em;
      font-weight: 300;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 4;
      overflow: hidden;
      text-overflow: ellipsis;
      width: calc(100% - 1em);
    }

    &__tags--list {
      display: block;
    }
    &__texts-container {
      font-size: 1.25em;
    }

  }
}
.view-list-small {
  .thumb {
    &__picture-container {
      width: 12em;
      min-width: 12em;
      height: 6.75em;
    }

    &__title {
      -webkit-line-clamp: 1;
    }

    &__texts-container {
      overflow: hidden;
      font-size: 1.125em;
    }
    &__bottom {
      width: calc(100% - 8em);
    }
  }

}
</style>
<script>
import {mapGetters} from "vuex";

export default {
  computed: {
    ...mapGetters('config', ['siteLogoSquare']),
  },
  props: {
    picture: String,
    logo: String,
    link: [String, Object],
    highlighted: Boolean,
  }
}
</script>
