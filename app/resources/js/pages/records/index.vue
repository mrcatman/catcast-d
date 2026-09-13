<template>
<div class="page-container">
  <div class="thumbs-list">
    <div class="thumbs-list__block" v-if="$store.state.userData.loggedIn && (loading.feed || feedRecords.length > 0) ">
      <div class="thumbs-list__header">
        <span class="thumbs-list__header__text">{{$t('records.feed')}}</span>
        <nuxt-link to="/records/feed"  class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
      </div>
      <div v-if="loading.feed" class="thumbs-list__preloader">
        <c-preloader  />
      </div>
      <div class="thumbs-list__items">
        <div :style="{width: (100 / itemsInRow) + '%'}" :key="item.id"  v-for="item in feedRecords" class="thumbs-list__item thumbs-list__item--auto-height">
          <RecordItem :data="item"/>
        </div>
      </div>
    </div>
    <div class="thumbs-list__block" v-if="$store.state.userData.loggedIn && (loading.popular || recommendedRecords.length > 0)">
      <div class="thumbs-list__header">
        <span class="thumbs-list__header__text">{{$t('records.recommended')}}</span>
        <nuxt-link to="/records/recommended"  class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
      </div>
      <div v-if="loading.popular" class="thumbs-list__preloader">
        <c-preloader  />
      </div>
      <div class="thumbs-list__items">
        <div :style="{width: (100 / itemsInRow) + '%'}" :key="item.id" v-for="item in recommendedRecords" class="thumbs-list__item thumbs-list__item--auto-height">
          <RecordItem :data="item"/>
        </div>
      </div>
    </div>
    <div class="thumbs-list__block">
      <div class="thumbs-list__header">
        <span class="thumbs-list__header__text">{{$t('records.popular')}}</span>
        <nuxt-link to="/records/popular"  class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
      </div>
      <div v-if="loading.popular" class="thumbs-list__preloader">
        <c-preloader  />
      </div>
      <div  ref="itemsList"  class="thumbs-list__items">
        <div :style="{width: (100 / itemsInRow) + '%'}" :key="item.id"  v-for="item in popularRecords" class="thumbs-list__item thumbs-list__item--auto-height">
          <RecordItem :data="item"/>
        </div>
      </div>
    </div>
    <div class="thumbs-list__block">
      <div class="thumbs-list__header">
        <span class="thumbs-list__header__text">{{$t('records.now_listening')}}</span>
        <nuxt-link to="/records/now-listening"  class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
      </div>
      <div v-if="loading.now_listening" class="thumbs-list__preloader">
        <c-preloader  />
      </div>
      <div class="thumbs-list__items">
        <div :style="{width: (100 / itemsInRow) + '%'}" :key="item.id"  v-for="item in nowWatchingRecords" class="thumbs-list__item thumbs-list__item--auto-height">
          <RecordItem :data="item"/>
        </div>
      </div>
    </div>
    <div class="thumbs-list__block">
      <div class="thumbs-list__header">
        <span class="thumbs-list__header__text">{{$t('records.new')}}</span>
        <nuxt-link to="/records/new"  class="thumbs-list__header__button">{{$t('global.show_more')}}</nuxt-link>
      </div>
      <div v-if="loading.new" class="thumbs-list__preloader">
        <c-preloader  />
      </div>
      <div class="thumbs-list__items">
        <div :style="{width: (100 / itemsInRow) + '%'}" :key="item.id"  v-for="item in newRecords" class="thumbs-list__item thumbs-list__item--auto-height">
          <RecordItem :data="item"/>
        </div>
      </div>
    </div>
  </div>
</div>
</template>
<script>
  let ResizeSensor = require('css-element-queries/src/ResizeSensor');
  import isMobile from '@/helpers/isMobile.js';
  import RecordItem from '@/components/RecordItemBig';
  export default {
    head () {
      return {
        title: this.$t('records.title'),
      }
    },
    components: {
      RecordItem
    },
    data() {
      return {
        itemsInRow: 1,
        isMobile: isMobile(),
        feed: {},
        popular: {},
        recommended: {},
        now_listening: {},
        new: {},
        loading: {
          feed: true,
          popular: true,
          best: true,
          now_listening: true,
          new: true,
        }
      }
    },
    async mounted() {
      if (!this.isMobile) {
        this.itemsInRow = Math.ceil(this.$refs.itemsList.offsetWidth / 800);
        new ResizeSensor(this.$refs.itemsList, (e) => {
          this.itemsInRow = Math.ceil(e.width / 800);
        });
      }
      if (this.$store.state.userData.loggedIn) {
        this.feed = (await this.$api.get('records?order=feed')).data.list;
        this.loading.feed = false;
      }
      if (this.$store.state.userData.loggedIn) {
        this.recommended = (await this.$api.get('records?order=recommended')).data.list;
        this.loading.recommended = false;
      }
      this.popular = (await this.$api.get('records?order=popularity')).data.list;
      this.loading.popular = false;
      this.now_listening = (await this.$api.get('records?order=now_listening')).data.list;
      this.loading.now_listening = false;
      this.new = (await this.$api.get('records?order=new')).data.list;
      this.loading.new = false;
    },
    computed: {
      feedRecords() {
        return this.feed.data ? this.feed.data : [];
      },
      popularRecords() {
        return this.popular.data ? this.popular.data : [];
      },
      recommendedRecords() {
        return this.recommended.data ? this.recommended.data : [];
      },
      nowWatchingRecords() {
        return this.now_listening.data ? this.now_listening.data : [];
      },
      newRecords() {
        return this.new.data ? this.new.data : [];
      },
    },
    methods: {

    }
  }
</script>
