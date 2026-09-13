<template>
  <nuxt-link :to="'/records/'+data.id" class="record-item">
    <div :style="{backgroundImage: 'url('+data.project_logo+')'}" class="record-item__cover">
      <div class="record-item__icon">
        <i class="material-icons">play_arrow</i>
      </div>
    </div>

    <div class="record-item__info">
      <div class="record-item__title">
        {{data.title}}
      </div>
      <div class="record-item__description" v-html="data.description"></div>
    </div>
    <div class="record-item__length">
      {{formatTime(data.length)}}
    </div>
  </nuxt-link>
</template>
<style lang="scss">
  .record-item {
    width: calc(100% - 1em);
    padding: .5em;
    display: flex;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, .05);
    text-decoration: none;
    position: relative;
    transition: all .4s;
    &:hover {
      background: rgba(255, 255, 255, .075);
    }
    &__cover {
      width: 3em;
      height: 3em;
      background-color: #000;
      background-size: contain;
      background-position: center center;
      background-repeat: no-repeat;
      position: relative;
    }

    &__description {
      font-size: .75em;
      font-weight: 300;
      p {
        margin: 0;
      }
    }
    &__icon {
      opacity: .75;
      position: relative;
      top: .125em;
    }
    &__info {
      padding: 0 0 0 1em;
      flex: 1;
    }
  }
</style>
<script>
  export default {
    methods: {
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
