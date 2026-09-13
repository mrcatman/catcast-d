<template>
  <div class="timetable-widget" ref="container">

    <c-modal inline v-model="panel.visible" :header="''" v-if="panel.item">
      <div slot="main">
        <div class="timetable-widget__window" >
          <div class="timetable-widget__window__header">
            <div :style="panel.item.picture ? `background: url('${panel.item.picture}') no-repeat center center; background-size: cover;` : ``" class="timetable-widget__window__picture">
              <div class="timetable-widget__window__picture__question-mark" v-if="!panel.item.picture">?</div>
            </div>
            <div class="timetable-widget__window__texts">
              <div class="timetable-widget__window__title">{{panel.item.title}}</div>
              <div class="timetable-widget__window__info">
                {{ $t('timetable.panel.time_start')}} <span class="timetable-widget__window__info__value">{{panel.item.readable_time}}</span>
              </div>
              <div class="timetable-widget__window__info">
                {{ $t('timetable.panel.length')}} <span class="timetable-widget__window__info__value">{{formatDuration(panel.item.length)}}</span>
              </div>
            </div>
          </div>
          <div class="timetable-widget__window__description">
            {{panel.item.description ? panel.item.description : $t('timetable.panel.no_description')}}
          </div>
          <div v-if="!currentItemInPast" class="timetable-widget__window__button-container">
            <AnnounceSubscribeButton :data="panel.item"/>
          </div>
        </div>
      </div>
      <!--
      <div class="modal__buttons" slot="buttons">
        <c-button icon="play_arrow" :loading="timetable.panel.loadingVideo"  v-if="canViewCurrentItem" @click="loadTimetablePanelVideo()">{{$t('timetable.panel.watch_now')}}</c-button>
      </div>
      -->
    </c-modal>


    <div class="timetable-widget__inner">
      <div class="timetable-widget__background"></div>
      <div class="timetable-widget__container">
        <div class="timetable-widget__header">
          <c-button flat icon-only icon="chevron_left" @click="changeDay(-1)" />
          <span class="timetable-widget__header__date">{{dayFormatted}}</span>
          <c-button flat icon-only icon="chevron_right" @click="changeDay(1)" />
        </div>
        <div ref="timetable-widget_container" class="timetable-widget__list-container">
          <div v-if="loading" class="timetable-widget__list__loading">
            <c-preloader />
          </div>
          <div class="timetable-widget__list">
            <div class="timetable-widget__nothing-found" v-if="!loading && list.length === 0">
              {{$t('timetable.nothing_found_for_day')}}
            </div>
            <a @click="showItemPanel(item)" class="timetable-widget__item" :class="{'timetable-widget__item--current': item.is_current, 'timetable-widget__item--playing': currentPlayingItem && item.id === currentPlayingItem.id}" :key="$index" v-for="(item, $index) in list">
              <div class="timetable-widget__item__inner">
                <div :style="item.picture ? `background: url('${item.picture}') no-repeat center center; background-size: cover;` : ``" class="timetable-widget__item__picture">
                  <div class="timetable-widget__item__picture__question-mark" v-if="!item.picture">?</div>
                </div>
                <div class="timetable-widget__item__info">
                  <div class="timetable-widget__item__date">{{getTime(item.time)}}</div>
                  <div class="timetable-widget__item__title">{{item.title}}</div>
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
  import { getTime, formatDuration  } from '@/helpers/dates.js';
  import { addDays, format } from 'date-fns';
  import AnnounceSubscribeButton from "@/components/buttons/AnnounceSubscribeButton";
  const locales = {
    en: require('date-fns/locale/en'),
    ru: require('date-fns/locale/ru')
  };
  export default {
    components: {AnnounceSubscribeButton},
    computed: {
      currentItemInPast() {
        if (this.panel.item) {
          let ts = Math.floor(new Date().getTime() / 1000);
          return this.panel.item.time <= ts;
        }
        return false;
      },
      canViewCurrentItem() {
        if (this.panel.item) {
          if (this.panel.item.can_view) {
            let ts = Math.floor(new Date().getTime() / 1000);
            return this.panel.item.time <= ts;
          }
        }
        return false;
      },
      dayFormatted() {
        return format(this.currentDay,
          "D MMMM",
          {locale: locales[window.__locale__]}
        );
      },
    },
    methods: {
      formatDuration,
      showItemPanel(item) {
        this.panel.item = item;
        this.panel.visible = true;
      },
      getTime,
      async changeDay(count) {
        this.currentDay = addDays(this.currentDay, count);
        this.loading = true;
        let dayFormatted = format(this.currentDay,
          "DD.MM.YYYY",
          {locale: locales[window.__locale__]}
        );
        let timetable = (await this.$axios.post(`timetable/getbychannel/${this.channel.id}?day=${dayFormatted}`)).data;
        this.list = Object.values(timetable.data.list);
        this.loading = false;
      },
      setColors() {
        this.channel.colors.forEach((color,index)=>{
          this.$refs.container.style.setProperty(`--channel-colors-${index}`, color);
        });
      }
    },
    data() {
      return {
        currentDay: new Date(),
        currentPlayingItem: null,
        loading: true,
        list: [],
        panel: {
          visible: false,
          item: null,
        }
      }
    },
    async mounted() {
      this.setColors();
      this.loading = true;
      let timetable = (await this.$axios.post(`timetable/getbychannel/${this.channel.id}`)).data;
      if (!timetable.status) {
        this.list = [];
      } else {
        this.list = Object.values(timetable.data.list);
      }
      this.loading = false;
    },
    props: {
      channel: {
        type: Object,
        required: true
      }
    }
  }
</script>
<style lang="scss">
  .timetable-widget {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--channel-colors-inside-texts);
    background: var(--channel-colors-inside-background);
    height: 100%;
    &__nothing-found {
      height: 100%;
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25em;
      text-align: center;
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
      height: 100%;
      overflow: auto;
    }
    &__header {
      text-align: center;
      width: 100%;
      position: relative;
      z-index: 10;
      padding: .5em 0;
      background: var(--channel-colors-inside-panels);
      &__date {
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
      background: var(--channel-colors-inside-panels);
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
      font-size: 1.25em;
      &--current {
        background: rgba(255, 255, 255, .1);
      }
      &--playing {
        background: var(--channel-colors-page-links);
      }
      &__info {
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
    &__window {
      max-width: 50vw;
      &__header {
        display: flex;
        align-items: center;
      }

      &__picture {
        width: 8em;
        height: 5em;
        background: rgba(0, 0, 0, .5);
        &__question-mark {
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 2.5em;
          font-weight: 600;
        }
      }

      &__texts {
        margin: 0 0 0 .5em;
      }

      &__title {
        font-size: 1.25em;
        font-weight: 500;
      }

      &__info {
        margin: .25em 0;
      }

      &__description {
        padding: 1em 0 0;
      }
      &__button-container {
        margin: 1em 0 0;
      }
    }
  }
</style>
