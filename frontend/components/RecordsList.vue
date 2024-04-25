<template>
  <c-infinite-scroll :loading="loading" @scroll="loadMore" class="thumbs-list">
    <div v-if="this.$slots.default" class="thumbs-list__header">
      <slot></slot>
    </div>
    <div v-else class="thumbs-list__header thumbs-list__header--default">
      <span class="thumbs-list__header__text">{{title}}</span>
    </div>
    <div class="thumbs-list__block" :class="{'thumbs-list__block--with-pager': list.last_page > 1}">
      <div class="thumbs-list__items" ref="itemsList">
        <div :style="{width: (100 / itemsInRow) + '%'}" :key="item.id" v-for="item in list.data" class="thumbs-list__item thumbs-list__item--auto-height">
          <RecordItem :data="item"/>
        </div>
      </div>
    </div>
    <c-preloader block  v-if="loadingInitial" />
    <div class="pager-vertical-container pager-vertical-container--mobile-default">
      <c-pager @pageChange="onChangePage" :vertical="!isMobile" :data="list" v-model="currentPage" />
    </div>
  </c-infinite-scroll>
</template>
<style lang="scss">

</style>
<script>
  import RecordItem from "@/components/RecordItemBig";
  let ResizeSensor = require('css-element-queries/src/ResizeSensor');
  import isMobile from '@/helpers/isMobile.js';
  import VideoItem from '@/components/VideoItem';
  let onPage = 30;
  export default {
    components: {
      RecordItem,
      VideoItem
    },
    async mounted() {
      if (!this.isMobile) {
        this.itemsInRow = Math.ceil(this.$refs.itemsList.offsetWidth / 800);
        new ResizeSensor(this.$refs.itemsList, (e) => {
          this.itemsInRow = Math.ceil(e.width / 800);
        });
      }
      this.loadingInitial = true;
      await this.load();
      this.loadingInitial = false;
    },
    props: {
      title: {
        type: String,
        required: false
      },
      searchParams: {
        type: String,
        required: true,
      }
    },
    computed: {
      getPagesCount() {
        if (this.total === 0) {
          return 0;
        }
        return Math.ceil(this.total/this.onPage);
      }
    },
    watch: {
      searchParams() {
        this.currentPage = 1;
        this.load();
      },
      currentPage() {

      }
    },
    data() {
      return {
        itemsInRow: 1,
        loading: false,
        loadingInitial: false,
        currentPage: 1,
        visiblePage: 1,
        isScrolling: false,
        isMobile: isMobile(),
        total: 0,
        list: {},
      }
    },
    created() {

    },
    methods: {
      async load() {
        this.loadingInitial = true;
        this.list = (await this.$api.get(this.getBaseURL(this.currentPage, onPage))).data.list;
        this.loadingInitial = false;
      },
      onChangePage(page) {
        this.currentPage = page;
        this.load();
      },
      getBaseURL(page=1, count=10) {
        return `/records?`+this.searchParams+`&page=${page}&count=${count}`;
      },
      loadMore() {
        if (!this.loading) {
          if (this.currentPage < this.list.last_page) {
            this.loading = true;
            this.currentPage++;
            this.$axios.get(this.getBaseURL(this.currentPage, onPage)).then(res => {
              this.list.data = [...this.list.data, ...res.data.data.list.data];
              this.loading = false;
            });
          }
        }
      }
    }
  }
</script>
