<template>
  <nuxt-link :to="'/records/'+data.id" class="record-item-big">
    <div class="record-item-big__inner">
      <div :style="{backgroundImage: 'url('+data.project_logo+')'}" class="record-item-big__cover">
        <div class="record-item__icon">
          <i class="material-icons">play_arrow</i>
        </div>
      </div>

      <div class="record-item-big__info">
        <div class="record-item-big__title">
          {{data.title}}
        </div>
        <div class="record-item-big__description" v-html="data.description"></div>
        <div class="record-item-big__params">
          <span v-if="data.length > 0" class="record-item-big__param">
            <i class="material-icons">access_time</i>
            <span class="record-item-big__param__text">{{formatTime(data.length)}}</span>
          </span>
          <span class="record-item-big__param">
            <i class="material-icons">headset</i>
            <span class="record-item-big__param__text">{{data.views}}</span>
          </span>
          <span v-if="data.created_at" class="record-item-big__param">
            <i class="material-icons">calendar_today</i>
            <span class="record-item-big__param__text">{{formatPublishDate(data.created_at, false)}}</span>
          </span>
        </div>
      </div>
    </div>
  </nuxt-link>
</template>
<style lang="scss">
  .record-item-big {
    margin: .25em;
    text-decoration: none;
    display: block;
    box-shadow: 0 .25em 1em -.75em var(--active-color);
    transition: all .4s;
    position: relative;
    top: 0;
    @media screen and (max-width: 768px) {
      margin: .25em .25em 0;
      width: calc(100% - .75em);
    }
    &:hover {
      background: rgba(255, 255, 255, .075);
      top: -.5em;
      box-shadow: 0 .5em 1em -.25em var(--active-color);
    }
    &__inner {
      padding: 1em;
      display: flex;
      background: var(--box-color);
    }

    &__cover {
      width: 5em;
      height: 5em;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #000;
      background-size: contain;
      background-position: center center;
      background-repeat: no-repeat;
      position: relative;
    }
    &__title {
      font-weight: 500;
    }
    &__params {
      font-size: .75em;
      display: flex;
      align-items: center;
      margin: 1em 0 0;
    }

    &__param {
      display: flex;
      align-items: center;
      margin: 0 .75em 0 0;
      &__text {
        margin: 0 0 0 .5em;
      }
    }

    &__description {
      margin: .35em 0 0;
      font-size: .95em;
      font-weight: 300;
      p {
        margin: 0;
      }
    }
    &__icon {
      opacity: .75;
    }
    &__info {
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 0 0 0 1em;
      flex: 1;
    }
  }
</style>
<script>
  import { formatPublishDate } from '@/helpers/dates.js';
  export default {
    methods: {
      formatPublishDate,
      formatTime(milliseconds) {
        let time = Math.floor(milliseconds / 100);
        let hrs = ~~(time / 3600);
        let mins = ~~((time % 3600) / 60);
        let secs = ~~time % 60;
        let ret = "";
        if (hrs > 0) {
          ret += "" + hrs + ":" + (mins < 10 ? "0" : "");
        }
        ret += "" + mins + ":" + (secs < 10 ? "0" : "");
        ret += "" + secs;
        return ret;
      },
    },
    props: {
      data: {
        type: Object,
        required: true
      }
    }
  }
</script>
