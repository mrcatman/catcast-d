<template>
<div class="page-container">
  <div class="thumbs-list">
    <div class="thumbs-list__block">
      <div class="thumbs-list__header">
        <span class="thumbs-list__header__text">{{$t('radio.online')}}</span>
        <nuxt-link to="/radio/online" class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
      </div>
      <div v-if="loading.online" class="thumbs-list__preloader">
        <c-preloader  />
      </div>
      <div class="thumbs-list__items" ref="itemsList">
        <div :style="{width: (100 / itemsInRow) + '%'}" :key="item.id"  v-for="item in onlineChannels" class="thumbs-list__item">
          <ChannelThumb :data="item"/>
        </div>
      </div>
    </div>
    <div class="thumbs-list__block">
      <div class="thumbs-list__header">
        <span class="thumbs-list__header__text">{{$t('radio.popular')}}</span>
        <nuxt-link to="/radio/popular" class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
      </div>
      <div v-if="loading.popular" class="thumbs-list__preloader">
        <c-preloader  />
      </div>
      <div class="thumbs-list__items">
        <div :style="{width: (100 / itemsInRow) + '%'}" :key="item.id" v-for="item in popularChannels" class="thumbs-list__item">
          <ChannelThumb :data="item"/>
        </div>
      </div>
    </div>
    <div class="thumbs-list__block">
      <div class="thumbs-list__header">
        <span class="thumbs-list__header__text">{{$t('radio.best')}}</span>
        <nuxt-link to="/radio/best" class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
      </div>
      <div v-if="loading.best" class="thumbs-list__preloader">
        <c-preloader  />
      </div>
      <div class="thumbs-list__items">
        <div :style="{width: (100 / itemsInRow) + '%'}" :key="item.id"  v-for="item in bestChannels" class="thumbs-list__item">
          <ChannelThumb :data="item"/>
        </div>
      </div>
    </div>
  </div>
</div>
</template>
<script>
  let ResizeSensor = require('css-element-queries/src/ResizeSensor');
  import isMobile from '@/helpers/isMobile.js';
  import ChannelThumb from '@/components/thumbs/ChannelThumb';
  export default {

    head () {
      return {
        title: this.$t('radio.heading'),
      }
    },
    components: {
      ChannelThumb
    },
    data() {
      return {
        itemsInRow: 1,
        isMobile: isMobile(),
        online: {},
        popular: {},
        best: {},
        loading: {
          online: true,
          popular: true,
          best: true,
        }
      }
    },
    async mounted() {
      if (!this.isMobile) {
        this.itemsInRow = Math.ceil(this.$refs.itemsList.offsetWidth / 300);
        new ResizeSensor(this.$refs.itemsList, (e) => {
          this.itemsInRow = Math.ceil(e.width / 300);
        });
      }
      this.online = (await this.$api.get('channels?type=radio&online=true&order=viewers')).data.list;
      this.loading.online = false;
      this.popular = (await this.$api.get('channels?type=radio&order=popularity')).data.list;
      this.loading.popular = false;
      this.best = (await this.$api.get('channels?type=radio&order=best')).data.list;
      this.loading.best = false;
    },
    computed: {
      onlineChannels() {
        return this.online && this.online.data ? this.online.data : [];
      },
      popularChannels() {
        return this.popular && this.popular.data  ? this.popular.data : [];
      },
      bestChannels() {
        return this.best && this.best.data  ? this.best.data : [];
      }
    },
    methods: {

    }
  }
</script>
