<template>
  <div class="feed-video-list">
    <nuxt-link :to="'/'+data.channel.shortname" class="feed-video-list__channel">
      <div :style="'background: url('+data.channel.logo+') no-repeat center center; background-size: cover;'" class="feed-video-list__channel__logo"></div>
      <div class="feed-video-list__channel__info">
        <div class="feed-video-list__channel__name-container">
          <span class="feed-video-list__channel__name">{{data.channel.name}}</span>
          <span class="feed-video-list__channel__added-videos">
            {{$t('feed.videos.added', [data.list.length])}}
          </span>
        </div>
        <div class="feed-video-list__channel__add-time">
          {{data.add_time}}
        </div>
      </div>
    </nuxt-link>
    <div class="feed-video-list__thumbs">
      <nuxt-link :title="video.title" :to="'/media/'+video.id" class="feed-video-list__thumb" :key="$index" v-for="(video, $index) in data.list">
        <div class="feed-video-list__thumb__inner">
          <div class="feed-video-list__thumb__picture"  :style="'background: url('+video.thumbnail+'); background-size: cover;'"></div>
          <div class="feed-video-list__thumb__title">{{video.title}}</div>
        </div>
      </nuxt-link>
    </div>
  </div>
</template>
<style lang="scss">
  .feed-video-list {
    &__channel {
      padding: .5em;
      border-bottom: 1px solid rgba(255, 255, 255, .1);
      margin: 0 0 1em;
      display: flex;
      align-items: center;
      text-decoration: none;
      &__logo {
        width: 2em;
        height: 2em;
      }

      &__info {
        margin: 0 0 0 1em;
      }

      &__name {
        font-weight: 600;
      }
    }

    &__thumbs {
      display: flex;
      justify-content: flex-start;
      flex-wrap: wrap;
      margin: 0 0 1em;
    }

    &__thumb {
      text-decoration: none;
      width: calc(100% / 5);
      &__inner {
        margin: .5em;
      }
      &__picture {
        width: 100%;
        padding-top: 60%;
      }
      &__title {
        margin: .5em 0 0;
        font-size: .75em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
    }
    @media screen and (max-width: 768px) {
      & {
        &__thumb {
          width: 50%;
        }
      }
    }
    @media screen and (max-width: 500px) {
      & {
        &__thumb {
          width: 100%;
          &__title {
            font-size: 1em;
            margin: .5em 0 1em;
          }
        }
      }
    }
  }
</style>
<script>
  export default {
    props: {
      data: {
        type: [Object],
        required: true
      }
    }
  }
</script>
