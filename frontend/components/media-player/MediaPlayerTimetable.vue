<template>
  <div class="media-player__timetable">
    <c-modal :inline="!detached" v-model="timetable.panel.visible" :header="''" v-if="timetable.panel.item">
      <div slot="main">
        <div class="media-player__timetable-window" >
          <div class="media-player__timetable-window__header">
            <div :style="timetable.panel.item.picture ? `background: url('${timetable.panel.item.picture}') no-repeat center center; background-size: contain;` : ``" class="media-player__timetable-window__picture">
              <div class="media-player__timetable-window__picture__question-mark" v-if="!timetable.panel.item.picture">?</div>
            </div>
            <div class="media-player__timetable-window__texts">
              <div class="media-player__timetable-window__title">{{timetable.panel.item.title}}</div>
              <div v-show="timetable.panel.item.readable_time" class="media-player__timetable-window__info">
                {{ $t('timetable.panel.time_start')}} <span class="media-player__timetable-window__info__value">{{timetable.panel.item.readable_time}}</span>
              </div>
              <div v-show="timetable.panel.item.length" class="media-player__timetable-window__info">
                {{ $t('timetable.panel.length')}} <span class="media-player__timetable-window__info__value">{{formatDuration(timetable.panel.item.length)}}</span>
              </div>
            </div>
          </div>
          <div class="media-player__timetable-window__description">
            {{timetable.panel.item.description ? timetable.panel.item.description : $t('timetable.panel.no_description')}}
          </div>
          <div v-if="!currentItemInPast" class="media-player__timetable-window__button-container">
            <AnnounceSubscribeButton :data="timetable.panel.item"/>
          </div>
        </div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <c-button icon="play_arrow" :loading="timetable.panel.loadingVideo"  v-if="canViewCurrentItem" @click="loadTimetablePanelVideo()">{{$t('timetable.panel.watch_now')}}</c-button>
      </div>
    </c-modal>

    <div class="media-player__timetable__inner">
      <div class="media-player__timetable__background"></div>
      <div class="media-player__timetable__container">
        <div class="media-player__timetable__header">
          <a class="media-player__timetable__header__button" @click="changeTimetableDay(-1)">
            <i class="material-icons">chevron_left</i>
          </a>
          <span class="media-player__timetable__header__date">{{(timetableDayFormatted)}}</span>
          <a class="media-player__timetable__header__button" @click="changeTimetableDay(1)">
            <i class="material-icons">chevron_right</i>
          </a>
        </div>
        <div ref="timetable_container" class="media-player__timetable__list-container">
          <div v-if="timetable.loading" class="media-player__timetable__list__loading">
            <c-preloader />
          </div>
          <div class="media-player__timetable__list">
            <div class="media-player__timetable__nothing-found" v-if="!timetable.loading && timetable.list.length === 0">
              {{$t('timetable.nothing_found_for_day')}}
            </div>
            <a  @click="showTimetableItemPanel(item)" class="media-player__timetable__item" :class="{'media-player__timetable__item--current': item.is_current, 'media-player__timetable__item--playing': item.id === timetable.currentPlayingItem.id}" :key="$index" v-for="(item, $index) in timetable.list">
              <div class="media-player__timetable__item__inner">
                <div :style="item.picture ? `background: url('${item.picture}') no-repeat center center; background-size: cover;` : ``" class="media-player__timetable__item__picture">
                  <div class="media-player__timetable__item__picture__question-mark" v-if="!item.picture">?</div>
                </div>
                <div class="media-player__timetable__item__info">
                  <div class="media-player__timetable__item__date">{{getTime(item.time)}}</div>
                  <div class="media-player__timetable__item__title">{{item.title}}</div>
                </div>
              </div>
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>
<script>
import {addDays, format} from "date-fns";
import { getLocales, getTime } from '@/helpers/dates.js';


export default {
  data() {
    return {
      video: null,
      screenWidthInPx: 0,
      widthInPx: 0,
      itemWidthInPx: 0,
      itemsVisible: 7,
      scroll: 0,
      visible: false,
      loading: false,
      loaded: false,
      list: [],
      currentPlaylist: [],
      currentPlayingItem: {},
      videoIndex: null,
      currentDay: new Date(),
      panel: {
        loadingVideo: false,
        visible: false,
        item: null,
      },
    }
  },
  methods: {
    getTime,
    timetableDayFormatted() {
      return format(this.timetable.currentDay,
        "D MMMM",
        {locale: locales[window.__locale__]}
      );
    },
    async changeTimetableDay(count) {
      this.timetable.currentDay = addDays(this.timetable.currentDay, count);
      this.timetable.loading = true;
      let dayFormatted = format(this.timetable.currentDay,
        "DD.MM.YYYY",
        {locale: this.locales[window.__locale__]}
      );
      let timetable = (await this.$axios.post(`timetable/getbychannel/${this.channel.id}?day=${dayFormatted}`)).data;
      this.timetable.list = Object.values(timetable.data.list);
      this.timetable.loading = false;
    },
    async loadVideo(id, playlist_id) {
      const query = playlist_id ? `?page_type=playlist&page_id=${playlist_id}` : '';
      const video = await this.$api.get(`/videos/${id}${query}`);
      this.$emit('loadVideo', video);
      this.timetable.video = video;
    },
    async loadTimetablePanelVideo() {
      this.timetable.panel.loadingVideo = true;
      await this.loadVideo(this.timetable.panel.item.video_id, this.timetable.panel.item.playlist_id);
      this.timetable.panel.loadingVideo = false;
      if (this.videoData) {
        this.timetable.playlist = JSON.parse(JSON.stringify(this.timetable.list));
        this.timetable.videoIndex = this.timetable.list.indexOf(this.timetable.panel.item);
        this.timetable.currentPlayingItem = JSON.parse(JSON.stringify(this.timetable.panel.item));
        this.timetable.visible = false;
        this.timetable.panel.visible = false;
      }
    },
    showTimetableItemPanel(item) {
      this.timetable.panel.item = item;
      this.timetable.panel.visible = true;
    },
    scrollTimetableForScreen(direction) {
      if (direction === -1) {
        if (this.timetable.scroll >  this.timetable.screenWidthInPx) {
          this.timetable.scroll -= this.timetable.screenWidthInPx;
        } else {
          this.timetable.scroll = 0;
        }
      } else {
        if (direction === 1) {
          if (this.timetable.scroll +  this.timetable.screenWidthInPx < ((this.timetable.widthInPx - this.timetable.screenWidthInPx))) {
            this.timetable.scroll += this.timetable.screenWidthInPx;
          } else {
            this.timetable.scroll = (this.timetable.widthInPx - this.timetable.screenWidthInPx);
          }
        }
      }
    },
    scrollTimetable(direction) {
      let change = direction * 50;
      if (this.timetable.scroll + change >=0 && this.timetable.scroll + change <= (this.timetable.widthInPx - this.timetable.screenWidthInPx)) {
        this.timetable.scroll += change;
      }
    },
    onTimetableScroll(e) {
      this.scrollTimetable(e.deltaY > 0 ? 1 : -1);
    },
    setVisibleTimetableItemsCount() {
      if (this.$refs.timetable_container) {
        let em = parseFloat(getComputedStyle(this.$refs.timetable_container).fontSize);
        let width = this.$refs.timetable_container.offsetWidth;
        this.timetable.itemsVisible = Math.floor(width / 200);
        this.timetable.screenWidthInPx = width - em * 2;
        this.timetable.widthInPx = width * (this.timetable.list.length / Math.floor(width / 200));
        this.timetable.itemWidthInPx = width / this.timetable.itemsVisible;
        let currentItem = this.timetable.list.filter(item => item.is_current)[0];
        if (currentItem) {
          let currentItemIndex = this.timetable.list.indexOf(currentItem);
          let scroll = (this.timetable.itemWidthInPx * currentItemIndex) - (this.timetable.itemsVisible * this.timetable.itemWidthInPx / 2);
          if (scroll < (this.timetable.widthInPx - this.timetable.screenWidthInPx)) {
            this.timetable.scroll = scroll;
          } else {
            this.timetable.scroll = this.timetable.widthInPx - this.timetable.screenWidthInPx;
          }
        }
      }
    },
    async showTimetable() {
      if (!this.timetable.visible) {
        this.timetable.visible = true;
        this.timetable.loading = true;
        let timetable = (await this.$axios.post(`timetable/getbychannel/${this.channel.id}`)).data;
        this.timetable.list = Object.values(timetable.data.list);
        this.timetable.loading = false;
        // this.setVisibleTimetableItemsCount();
      } else {
        this.timetable.visible = false;
      }
    },
    async changeTimetableItemIndex(index) {
      this.timetable.videoIndex = index;
      this.timetable.panel.loadingVideo = true;
      this.timetable.panel.item = this.timetable.playlist[this.timetable.videoIndex];
      await this.loadVideo(this.timetable.panel.item.video_id, this.timetable.panel.item.playlist_id);
      this.timetable.panel.loadingVideo = false;
      if (this.videoData) {
        this.timetable.currentPlayingItem = JSON.parse(JSON.stringify(this.timetable.panel.item));
      }
    },
    currentItemInPast() {
      if (this.panels.timetable.panel.item) {
        let ts = Math.floor(new Date().getTime() / 1000);
        return this.panels.timetable.panel.item.time <= ts;
      }
      return false;
    },
    canViewCurrentItem() {
      if (this.panels.timetable.panel.item) {
        if (this.panels.timetable.panel.item.can_view) {
          let ts = Math.floor(new Date().getTime() / 1000);
          return this.timetable.panel.item.time <= ts;
        }
      }
      return false;
    },
    async backToLiveBroadcast() {
      // this.panels.timetable.isPlayingVideo = false;
      // this.panels.timetable.videoIndex = null;
      // this.panels.timetable.currentPlayingItem = {};
      this.videoData = null;
      this.initPlayer(true);
    },
  }
}
</script>
<style lang="scss" scoped>
.media-player {
  &__timetable {
    position: absolute;
    overflow: hidden;
    top: 0;
    height: 100%;
    right: 0;
    display: flex;
    align-items: center;
    justify-content: center;

    &__nothing-found {
      height: 100%;
      display: flex;
      align-items: center;
      padding: 0 1em;
      width: calc(100% - 2em);
    }

    &__arrow {
      padding: 1em;
      cursor: pointer;
      position: relative;
      font-size: 1.25em;
    }

    &__list-container {
      width: 100%;
      flex: 1;
      overflow: auto;
      position: relative;
    }

    &__container {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
      overflow: auto;
      height: calc(100% - 5em);
      padding: 2.5em 0;
    }

    &__header {
      position: relative;
      z-index: 10;
      padding: .5em 0;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.05);

      &__button {
        color: var(--channel-colors-inside-texts);
        height: 1.65em;
        cursor: pointer;
        padding: 0 .25em;
        background: rgba(255, 255, 255, 0.05);
        border-radius: .125em;
        display: inline-block;
        transition: all .4s;

        &:hover {
          background: rgba(255, 255, 255, 0.1);
        }
      }

      &__date {
        font-weight: 500;
        margin: 0 1em;
      }
    }

    &__inner {
      width: 100%;
      height: 100%;
      position: relative;
    }

    &__background {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: var(--channel-colors-inside-background);
      opacity: .5;
    }

    &__list {
      transition: all .4s;
      padding: 0;
      position: relative;
      z-index: 1;
      width: 100%;
      height: 100%;

      &__loading {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
      }
    }

    &__item {
      cursor: pointer;

      &--current {
        background: rgba(255, 255, 255, .1);
      }

      &--playing {
        background: var(--channel-colors-page-links);
      }

      &__info {
        width: 25vw;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0 0 0 .5em;
      }

      &__inner {
        border-bottom: 1px solid rgba(255, 255, 255, .1);
        position: relative;
        padding: .5em;
        display: flex;
        align-items: center;
      }

      &__title {
        margin: .5em 0 0;
        font-size: .75em;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 100%;
      }

      &__picture {
        background: rgba(0, 0, 0, 0.25);
        width: 5em;
        height: 3em;
        padding: 0;
        background-size: cover !important;

        &__question-mark {
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 2em;
          font-weight: 600;
        }
      }

      &__date {
        font-size: .875em;
        font-weight: 700;
      }
    }
  }
}
</style>
