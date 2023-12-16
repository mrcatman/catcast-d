<template>
  <div class="videos-section">
    <c-preloader block  v-if="loadingInitial" />
    <div class="videos-section__header">
      <div class="videos-section__search">
        <c-input @change="reload()" :loading="search.loading" v-model="search.text" :inlinePlaceholder="true" icon="search"></c-input>
      </div>
      <div class="videos-section__right">
        <div class="videos-section__view-change">
          <a @click="currentView = 'grid'" class="videos-section__view-change__item" :class="{'videos-section__view-change__item--active': currentView === 'grid'}">
            <i class="fa fa-th-large"></i>
          </a>
          <a @click="currentView = 'list'" class="videos-section__view-change__item" :class="{'videos-section__view-change__item--active': currentView === 'list'}">
            <i class="fa fa-list-ul"></i>
          </a>
        </div>
        <div class="videos-section__order">
          <c-select  v-model="order"  :options="orderOptions" />
        </div>
      </div>
    </div>
    <c-nothing-found v-if="!loading && videos.data && videos.data.length === 0" :title="$t('global.nothing_found')"/>
    <c-infinite-scroll v-else ref="items" :loading="loading" @scroll="loadMore" class="videos-section__items" :class="'videos-section__items--'+currentView" >
       <div :style="videoItemSize" :key="$index"  class="videos-section__item" v-for="(video, $index) in videos.data">
          <VideoItem :listView="currentView === 'list'" :data="video" />
      </div>
    </c-infinite-scroll>
  </div>
</template>
<script>
  import VideoItem from '@/components/VideoItem';
  let ResizeSensor = require('css-element-queries/src/ResizeSensor');
  import isMobile from '@/helpers/isMobile.js';
  const count = 24;
  export default {
    components: {
      VideoItem
    },
    watch: {
      currentView(newView) {
        localStorage.videos_current_view = newView;
      },
      order() {
        this.reload();
      }
    },
    computed: {
      videoItemSize() {
          let data = {};
          data.width = this.currentView === 'list' ? '100%' : ((100 / this.itemsInRow) + '%');
       //   data.paddingTop = this.currentView === 'list' ? '' : `calc(${((100 / this.itemsInRow) * 9 / 16)}% + 3em)`;
         return data;
      },
      orderOptions() {
        return [
          {'name': this.$t('videos.order.new'), 'value': 'new'},
          {'name': this.$t('videos.order.old'), 'value': 'old'},
          {'name': this.$t('videos.order.popular'), 'value': 'popular'},
        ]
      },
      url() {
        let params = this.searchParams;
        if (params.indexOf('?') !== -1) {
          params+= "&count="+count;
        } else {
          params+= "?count="+count;
        }
        params+="&page="+this.currentPage+"&order="+this.order+"&search="+this.search.text;
        return params;
      }
    },
    async mounted() {
     // if (!isMobile()) {
        this.itemsInRow = Math.ceil(this.$refs.items.$el.offsetWidth / 360);
        new ResizeSensor(this.$refs.items.$el, (e) => {
          this.itemsInRow = Math.ceil(e.width / 360);
        });
     // }
      if (!this.initialData) {
        let videos = (await this.$api.get(this.url)).data;
        this.videos = videos;
      } else {
        this.videos = this.initialData;
      }
      this.loadingInitial = false;
      this.$parent.$on('scrollBottom', () => {
        this.loadMore();
      })
    },
    data() {
      return {
        itemsInRow: 1,
        loading: false,
        loadingInitial: true,
        videos: {},
        currentPage: 1,
        search: {
          loading: false,
          text: ''
        },
        order: 'new',
        currentView: localStorage.videos_current_view || 'grid',
      }
    },
    methods: {
      async reload() {
        this.currentPage = 1;
        this.loadingInitial = true;
        let videos = (await this.$api.get(this.url)).data;
        this.videos = videos;
        this.loadingInitial = false;
      },
      loadMore() {
        if (!this.loading) {
          if (this.currentPage < this.videos.last_page) {
            this.currentPage++;
            this.loading = true;
            this.$axios.get(this.url).then(res => {
              this.videos.data = [...this.videos.data, ...res.data.data.data];
              this.loading = false;
            })
          }
        }
      }
    },
    props: {
      initialData: {
        type: Object,
        required: false,
      },
      searchParams: {
        type: String,
        required: true,
      }
    }
  }
</script>
<style lang="scss">
  .videos-section {
    width: 100%;
    max-height: 100%;
    position: relative;
    display: flex;
    flex-direction: column;


    &__header {
      z-index: 1;
      background: var(--title-box-color);
      padding: 1em;
      display: flex;
      align-items: center;
      justify-content: space-between;
      --vertical-margin: 0;
    }
    &__search {
      flex: 1;
    }
    &__search .input__container {
      margin: 0;
    }
    &__right {
      flex: 2;
      display: flex;
      justify-content: flex-end;
      align-items: center;
    }

    &__view-change__item {
      cursor: pointer;
      padding: .5em;
      width: 1.25em;
      display: inline-block;
      text-align: center;
      border-radius: 0;
      margin: 0 .5em 0 0;
      transition: all .4s;
      &:hover {
        background: rgba(255, 255, 255, 0.05);
      }
      &--active {
        background: rgba(255, 255, 255, .1);
      }
    }
    &__order {
      position: relative;
      z-index: 1000000;
    }
    .channel-layout__bottom__inner & {
      height: unset;
    }

    .channel-layout__bottom__inner &__header {
      background: var(--channel-colors-page-buttons);
    }
    .channel-layout__bottom__inner &__items {
      overflow: hidden;
    }
    .channel-layout__bottom__inner--scrollable &__items {
      overflow: auto;
    }

    @media screen and (max-width: 768px) {
      & {
        overflow-x: hidden;
        &__header {
          flex-direction: column;
        }

        &__search {
          margin: 0 0 .5em;
          width: 100%;
        }

        &__right {
          justify-content: space-between;
          width: 100%;
        }

        &__item {
          width: 100%;
          padding: 0;
        }
      }
    }
  }
</style>
