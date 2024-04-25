<template>
  <div class="playlists-list">
    <div class="playlists-list__header">
      <span class="playlists-list__header__text">{{$t('playlists.heading')}}</span>
    </div>
    <div class="playlists-list__inner" ref="itemsList">
      <c-preloader block  v-if="loadingInitial" />
      <c-infinite-scroll :loading="loading" @scroll="loadMore" class="playlists-list__items">
        <project-item :style="{width: (100 / itemsInRow) + '%'}" :data="item" :key="$index" v-for="(item, $index) in list.data"/>
      </c-infinite-scroll>
      <div class="pager-vertical-container pager-vertical-container--mobile-default">
        <c-pager @pageChange="onPageChange" :vertical="!isMobile" :count="getPagesCount" v-model="currentPage" />
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .playlists-list {
    display: flex;
    flex-direction: column;
    height: 100%;
    @media screen and (max-width: 768px) {
      padding: 0 1em;
    }
    &__header {
      display: flex;
      align-items: center;
      background: var(--title-box-color);
      padding: 1em;
      text-decoration: none;
      font-size: 1.25em;
      font-weight: 600;
    }
    &__inner {
      flex: 1;
      height: calc(100% - 4em);
      overflow: hidden;
    }
    &__items {
      width: calc(100% - 7em);
      height: 100%;
      overflow: auto;
      display: flex;
      flex-wrap: wrap;
      @media screen and (max-width: 768px) {
        width: 100%;
      }
    }
  }
</style>
<script>
  let ResizeSensor = require('css-element-queries/src/ResizeSensor');
  import isMobile from '@/helpers/isMobile.js';
  import ProjectItem from '@/components/thumbs/PlaylistThumb.vue';
  let onPage = 10;
  export default {
    head() {
      return {
        title: this.$t('playlists.heading')
      }
    },
    components: {
      ProjectItem
    },
    computed: {
      getPagesCount() {
        if (!this.list.last_page) {
          return 0;
        }
        return this.list.last_page;
      }
    },
    watch: {

    },
    data() {
      return {
        loadingInitial: true,
        loading: false,
        currentPage: 1,
        isScrolling: false,
        isMobile: isMobile(),
        total: 0,
        list: {
          data: {}
        },
        itemsInRow: 1,
      }
    },
    async mounted() {
      if (!this.isMobile) {
        this.itemsInRow = Math.ceil(this.$refs.itemsList.offsetWidth / 240);
        new ResizeSensor(this.$refs.itemsList, (e) => {
          this.itemsInRow = Math.ceil(e.width / 240);
        });
      }
      this.list = (await this.$api.get(this.getBaseURL(1))).data.list;
      this.loadingInitial = false;
    },
    methods: {
      async onPageChange(newPage) {
        this.loading = true;
        this.list = (await this.$api.get(this.getBaseURL(newPage))).data.list;
        this.loading = false;
        if (this.isMobile) {
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          })
        }
      },
      getBaseURL(page = 1) {
        let count = (this.isMobile ? 10 : (this.itemsInRow * 3));
        return `/playlists?page=${page}&count=${count}`;
      },
      loadMore() {
        if (!this.loading) {
          if (this.currentPage < this.list.last_page) {
            this.loading = true;
            this.currentPage++;
            this.$axios.get(this.getBaseURL(this.currentPage)).then(res => {
              this.list.data = [...this.list.data, ...res.data.data.list.data];
              this.loading = false;
            });
          }
        }
      }
    }
  }
</script>

<style scoped>

</style>
