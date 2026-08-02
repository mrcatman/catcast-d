<template>
  <a @click="goLink()" class="video-item" :class="{'video-item--view-list': listView}">
    <div class="video-item__inner" ref="inner">
      <div  class="video-item__picture-container">
        <div class="video-item__preload-icon">
          <i class="fa fa-film"></i>
        </div>
        <div :style="{backgroundImage: 'url('+(data.thumbnail)+')'}" class="video-item__picture"></div>
      </div>
      <div class="video-item__info">
        <div class="video-item__title" >{{data.title}}</div> <!--v-show="width > 0" :style="{'max-width': width+'px'}"-->
        <div class="video-item__params">
          <span v-if="data.length > 0" class="video-item__param video-item__param--length">
            <i class="material-icons">access_time</i>
            <span class="video-item__param__text">{{formatDuration(data.length)}}</span>
          </span>
          <span class="video-item__param video-item__param--views">
            <i class="material-icons">visibility</i>
            <span class="video-item__param__text">{{data.views}}</span>
          </span>
          <span v-if="data.add_time" class="video-item__param video-item__param--time">
            <i class="material-icons">calendar_today</i>
            <span class="video-item__param__text">{{formatPublishDate(data.add_time)}}</span>
          </span>
        </div>
        <div class="video-item__channel" v-if="data.channel">
          <div class="video-item__channel__logo" :style="{backgroundImage: 'url('+(data.channel.logo)+')'}"></div>
          <span class="video-item__channel__name">{{data.channel.name}}</span>
        </div>
      </div>
      <div class="video-item__description">
        {{data.description}}
      </div>
    </div>
  </a>
</template>
<style lang="scss">
  .video-item {
    width: 100%;
    height: 100%;
    text-decoration: none;
    display: block;
    overflow: visible;
    &__description {
      display: none;
    }
    &__info {

    }
    &__params {
      font-size: .875em;
      white-space: nowrap;
      display: flex;
      align-items: center;
    }
    &__param {
      display: flex;
      align-items: center;
      opacity: .85;
      padding: .25em .5em;
      background: rgba(0, 0, 0, 0.75);
      border-radius: var(--border-radius);
      &__text {
        margin: 0 0 0 .5em;
      }
      &--length {
        position: absolute;
        top: .5em;
        left: .5em;
        z-index: 10000;
      }
      &--views {
        position: absolute;
        top: .5em;
        right: .5em;
        z-index: 100000;
      }
      &--time {
        position: absolute;
        left: .5em;
        z-index: 100000;
        font-size: .875em;
        top: calc(60% - 1em);
      }
    }
    &__title {
      min-height: 1.25em;
      text-overflow: ellipsis;
      overflow: hidden;
      font-weight: 500;
      font-size: 1em;
      line-height: 1.5;
      padding: .5em 0 .25em;
    }
    &__inner{
      display: flex;
      flex-direction: column;
      cursor: pointer;
      transition: all .4s;
      position: relative;
      top: 0;
      box-shadow: 0 0 .5em rgba(0, 0, 0, 0);
      overflow: hidden;
      background: none!important;
      width: calc(100% - 1em);
      padding: .5em;
      &:hover {
        top: -.5em;
        box-shadow: 0 .5em .5em var(--active-color);
      }
    }
    &__picture-container {
      height: 0;
      padding-top: 60%;
      background-color: rgba(0, 0, 0, .25);
      position: relative;
      flex: 1;
      background-size: cover;
      background-position: center center;
      background-repeat: no-repeat;
      &--thumbnail{
        background-size: cover;
      }
    }
    &__picture {
      position: absolute;
      z-index: 10000;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-size: cover;
      background-position: center center;
      background-repeat: no-repeat;
    }
    &__preload-icon {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3.5em;
      opacity: .25;
      position: absolute;
      top: 0;
      left: 0;
    }


    &__length {
      position: absolute;
      top: .25em;
      right: .25em;
      font-size: .75em;
      font-weight: 600;
    }
    &__channel {
      display: flex;
      align-items: center;
      &__logo {
        width: 1.5em;
        height: 1.5em;
        background-size: contain;
        background-position: center center;
        background-repeat: no-repeat;
      }
      &__name {
        font-size: 1em;
        margin: 0 0 0 .5em;
        font-weight: 300;
      }
    }

    &--view-list {
      overflow: visible;
    }
    &--view-list &__inner {
      flex-direction: row;
      align-items: center;
      position: relative;
      background: var(--box-footer-color);
      margin: 0 0 .125em!important;
      height: unset;
      &:hover {
        background: rgba(255, 255, 255, 0.05);
        box-shadow: 0 0.25em .75em .25em var(--active-color);
        z-index: 1000;
      }
    }

    &--view-list &__params {
      float: left;
      margin: .5em .5em 0 .5em;
    }
    &--view-list &__param {
      position: unset!important;
      margin: 0 1em 0 0;
      font-size: .75em;
      background: none;
      padding: 0;
    }
    &--view-list &__picture-container {
      flex: unset;
      padding: 0;
      max-height: 100%;
      width: 10em!important;
      height: 6em;
    }

    &--view-list &__title {
      font-size: .875em;
      padding: .5em .5em .25em;
      max-width: none!important;
      flex: 1;
    }

    &--view-list &__info {
      font-size: 1.25em;
      background: none;
      padding: 0;
      margin: 0 0 0 .5em;
      text-align: left;
      max-width: calc(100% - 10em);
      @media screen and (max-width: 768px) {
        font-size: 1em;
      }
    }
    &--view-list &__channel {
      font-size: .875em;
      background: none;
      width: 100%;
      padding: .5em 0 0 .5em;
    }

    @media screen and (max-width: 768px) {
      &__params {
        font-size: 1em;
      }

      &__title {
        font-size: 1em;
      }
    }
  }
</style>
<script>
  import { formatPublishDate, formatDuration } from '@/helpers/dates';
  export default {
    data() {
      return {
        width: null,
      }
    },
    mounted() {
      this.width = this.$refs.inner.offsetWidth;
    },
    props: {
      listView: {
        type: [Boolean],
        required: false,
      },
      link: {
        type: [Boolean],
        required: false,
        default: true,
      },
      data: {
        type: [Object],
        required: true
      }
    },
    methods: {
      formatDuration,
      formatPublishDate,
      goLink() {
        if (this.link) {
          this.$router.push(`/media/${this.data.id}`);
        }
      }
    }
  }
</script>
