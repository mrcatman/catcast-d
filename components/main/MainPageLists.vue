<template>
  <div class="main-page-lists" ref="lists">
    <div class="main-page__channel" v-if="currentChannel">
      <div class="main-page__channel__player">
        <div class="main-page__channel__player__inner">
          <media-player :channel="currentChannel"/>
        </div>
      </div>
      <div class="main-page__channel__info">
        <div class="main-page__channel__name">{{currentChannel.name}}</div>
        <div v-if="currentChannel.description && currentChannel.description.length > 0" v-html="currentChannel.description" class="main-page__channel__description"></div>
        <div class="main-page__channel__go-to-page">
          <c-button :to="'/'+currentChannel.shortname">{{$t('landing.go_to_channel_page')}}</c-button>
        </div>
        <div class="main-page__channel__counts">
          <div class="text-with-icon__list">
            <span class="text-with-icon" v-if="currentChannel.viewers">
              <i class="material-icons">remove_red_eye</i>
              <span class="text-with-icon__content">{{currentChannel.viewers}}</span>
            </span>
            <span class="text-with-icon">
              <i class="material-icons">thumb_up</i>
              <span class="text-with-icon__content">{{currentChannel.likes_count}}</span>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="thumbs-list__block" ref="channelsList">
          <div class="thumbs-list__header">
            <span class="thumbs-list__header__text">{{$t('channels._title')}}</span>
            <nuxt-link to="/tv" class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
          </div>
          <div v-if="loading.channels" class="thumbs-list__items">
            <preloading-thumb  :style="{width: (100 / itemsInRow.channels) + '%'}" v-for="$index in 24" :key="$index"/>
          </div>
          <div v-else class="thumbs-list__items">
            <div :style="{width: (100 / itemsInRow.channels) + '%'}" :key="item.id" v-if="item" v-for="item in lists.channels.data" class="thumbs-list__item">
              <ChannelThumb :data="item"/> <!--  :link="false" @click.native="setCurrentChannel(item)" -->
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="thumbs-list__block" ref="MediaList">
          <div class="thumbs-list__header">
            <span class="thumbs-list__header__text">{{$t('videos.title')}}</span>
            <nuxt-link to="/videos" class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
          </div>
          <div v-if="loading.videos" class="thumbs-list__items">
            <preloading-thumb  :style="{width: (100 / itemsInRow.videos) + '%'}" v-for="$index in 24" :key="$index"/>
          </div>
          <div v-else class="thumbs-list__items">
            <div :style="{width: (100 / itemsInRow.videos) + '%'}" :key="item.id" v-if="item" v-for="item in lists.videos.data" class="thumbs-list__item">
              <VideoItem :data="item"/>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="thumbs-list__block" ref="recordsList">
          <div class="thumbs-list__header">
            <span class="thumbs-list__header__text">{{$t('records.title')}}</span>
            <nuxt-link to="/records" class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
          </div>
          <div v-if="loading.records" class="thumbs-list__preloader">
            <c-preloader  />
          </div>
          <div v-else class="thumbs-list__items">
            <div :style="{width: (100 / itemsInRow.records) + '%'}"  :key="item.id" v-if="item" v-for="item in lists.records.data" class="thumbs-list__item thumbs-list__item--auto-height">
              <RecordItemBig :data="item"/>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="thumbs-list__block" ref="radioList">
          <div class="thumbs-list__header">
            <span class="thumbs-list__header__text">{{$t('radio._title')}}</span>
            <nuxt-link to="/radio" class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
          </div>
          <div v-if="loading.radio" class="thumbs-list__items">
            <preloading-thumb  :style="{width: (100 / itemsInRow.radio) + '%'}" v-for="$index in 24" :key="$index"/>
          </div>
          <div v-else class="thumbs-list__items">
            <div :style="{width: (100 / itemsInRow.radio) + '%'}" :key="item.id" v-if="item" v-for="item in lists.radio.data" class="thumbs-list__item">
              <ChannelThumb :data="item"/>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="main-page-lists__blog" ref="blogList">
          <nuxt-link to="/blog" class="thumbs-list__header">{{$t('blog._title')}}</nuxt-link>
          <div v-if="loading.blog" class="thumbs-list__preloader">
            <c-preloader  />
          </div>
          <div v-else class="main-page-lists__blog__inner">
            <div :key="$index" v-for="(item, $index) in lists.blog.data" :style="{width: (100 / itemsInRow.blog) + '%'}" class="main-page-lists__blog__item-container">
              <nuxt-link :to="'/blog/'+item.id" class="box box--with-header blog-list-item">
                <div class="box__header">
                  <div class="box__header__title">{{item.title}}</div>
                </div>
                <div class="box__inner">
                  <div class="blog-list-item__contents" v-html="item.short_text"></div>
                </div>
                <div class="box__footer">
                  <div class="blog-list-item__info">
                    <div class="text-with-icon__list">
                      <span class="text-with-icon">
                        <i class="material-icons">access_time</i>
                        <span class="text-with-icon__content">{{formatPublishDate(item.time)}}</span>
                      </span>
                      <span class="text-with-icon">
                        <i class="material-icons">remove_red_eye</i>
                        <span class="text-with-icon__content">{{item.views}}</span>
                      </span>
                      <span class="text-with-icon">
                        <i class="material-icons">thumb_up</i>
                        <span class="text-with-icon__content">{{item.likes_count}}</span>
                      </span>
                    </div>
                  </div>
                </div>
              </nuxt-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .main-page-lists {
    &__blog {
      &__inner {
        display: flex;
        flex-wrap: wrap;
      }
    }
    @media screen and (max-width: 768px) {
      .row {
        flex-direction: column;
      }

      .blog-list-item {
        margin: 0 0 1em;
      }

      .col {
        margin: 0 !important;
      }
    }
  }
  .main-page {
    &__channel {
      max-width: 1400px;
      margin: 3em auto;
      display: flex;
      @media screen and (max-width: 768px) {
        flex-direction: column;
        margin: 1em;
      }
      &__counts {
        font-size: 1.25em;
        margin: 1em 0 0;
        opacity: .75;
        font-weight: 500;
      }

      &__info {
        padding: 1em;
        background: var(--box-element-color);
        margin: 0 0 0 1em;
        border-radius: .25em;
        box-shadow: 0 .5em 1em -.5em var(--active-color);
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 100%;
        flex: .5;
        overflow: hidden;
        @media screen and (max-width: 768px) {
          margin: 1em 0 0;
        }
      }
      &__go-to-page {
        margin: 1em 0 0;
      }
      &__name {
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 500;
        font-size: 1.5em;
        white-space: nowrap;
      }

      &__player {
        width: calc(100% - 10em);
        position: relative;
        flex: 1;
        @media screen and (max-width: 768px) {
          width: 100%;
        }
        .media-player {
          position: absolute;
          top: 0;
          left: 0;
        }
        &__inner {
          position: relative;
          width: 100%;
          padding-top: 60%;
        }

        &__description {
          margin: .5em 0 0;
        }
      }
    }
  }
</style>
<script>
  import { formatPublishDate } from '@/helpers/dates.js';
  let ResizeSensor = require('css-element-queries/src/ResizeSensor');
  import ChannelThumb from '@/components/thumbs/ChannelThumb';
  import PreloadingThumb from '@/components/thumbs/PreloadingThumb';
  import VideoItem from '@/components/VideoItem';
  import RecordItemBig from "@/components/RecordItemBig";
  export default {
    components: {
      PreloadingThumb,
      RecordItemBig,
      VideoItem,
      ChannelThumb
    },
    methods: {
      formatPublishDate,
      setCurrentChannel(item) {
        this.currentChannel = null;
        this.$nextTick(() => {
          this.currentChannel = item;
          this.$refs.lists.scrollTo({
            top: 0,
            behavior: "smooth"
          });
        })
      },
      onScroll() {
        this.$nextTick(() => {
          let element = this.$refs.lists;
          if (element.scrollTop === 0) {
            this.$emit('input', false);
          } else {
            this.$emit('input', true);
          }
        })
      }
    },
    beforeDestroy() {
      this.$refs.lists.removeEventListener('scroll', this.onScroll);
    },
    async mounted() {
      if (!this.isMobile) {
        let onResize = () => {
          this.itemsInRow.channels = this.$refs.channelsList.offsetWidth > 500 ? Math.ceil(this.$refs.channelsList.offsetWidth / 300) : 1;
          this.itemsInRow.videos = this.$refs.MediaList.offsetWidth > 500 ?  Math.ceil(this.$refs.MediaList.offsetWidth / 300) : 1;
          this.itemsInRow.radio = this.$refs.radioList.offsetWidth > 500 ?  Math.ceil(this.$refs.radioList.offsetWidth / 300) : 1;
          this.itemsInRow.records = this.$refs.recordsList.offsetWidth > 500 ?  Math.ceil(this.$refs.recordsList.offsetWidth / 600) : 1;
          this.itemsInRow.blog = this.$refs.blogList.offsetWidth > 500 ?  Math.ceil(this.$refs.recordsList.offsetWidth / 800) : 1;
        };
        onResize();
        new ResizeSensor(this.$refs.channelsList, (e) => {
          this.$nextTick(() => {
            onResize();
          })

        });
      }
      this.$nextTick(async () => {
        let channelsCount =  this.itemsInRow.channels * 5;
        let videosCount = this.itemsInRow.videos * 5;
        let radioCount = this.itemsInRow.radio * 5;
        let recordsCount = this.itemsInRow.records * 5;
        let urls = {
          channels: 'channels?type=tv&online=true&order=viewers&cache=1&count='+channelsCount,
          videos: 'videos?order=popularity&count='+videosCount,
          radio: 'channels?type=radio&online=true&cache=1&order=viewers&count='+radioCount,
          records: 'records?count='+recordsCount,
          blog: `blog?count=6`
        };
        Object.keys(urls).forEach(key => {
          this.$axios.get(urls[key]).then(res => {
            this.lists[key] = res.data.data.list;
            if (key === 'channels') {
              if (res.data.data.channel_to_show) {
                //this.currentChannel = res.data.data.channel_to_show;
              }
            }
            this.loading[key] = false;
          });
        });
      });

      this.$refs.lists.addEventListener('scroll', this.onScroll);
    },
    props: {

    },
    data() {
      return {
        currentChannel: null,
        itemsInRow: {
          channels: 1,
          videos: 1,
          radio: 1,
          records: 1,
          blog: 1
        },
        lists: {
          channels: {},
          video: {},
          radio: {},
          records: {},
          blog: {}
        },
        loading: {
          channels: true,
          videos: true,
          radio: true,
          records: true,
          blog: true
        }
      }
    }
  }
</script>
