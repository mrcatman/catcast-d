<template>
<div class="feed">
  <div class="box feed__types">
    <div class="feed__type">
      <c-checkbox :title="$t('feed.types.news')" v-model="types.news"/>
    </div>
    <div class="feed__type">
      <c-checkbox :title="$t('feed.types.videos')" v-model="types.videos"/>
    </div>
    <div class="feed__type">
      <c-checkbox :title="$t('feed.types.posts')" v-model="types.posts"/>
    </div>
  </div>

  <div class="feed__inner">
    <c-preloader block  v-if="loading" />
    <c-infinite-scroll v-else :loading="loadingOnScroll" @scroll="loadMore" class="feed__scroll">
      <div class="feed__item" :key="$index" v-for="(item, $index) in feedList">
        <comment v-if="item.type === 'news' || item.type == 'posts'" :showPanel="false" :data="item.data" :key="item.data.id" />
        <FeedVideoList :data="item.data" v-else-if="item.type === 'videos'"/>
      </div>
    </c-infinite-scroll>
  </div>
</div>
</template>
<style lang="scss">
  .feed {
    width: 100%;
    height: 100%;
    display: flex;
    &__item {
      padding: 0 1em;
    }
    &__inner {
      position: relative;
      width: 100%;
      height: 100%;
    }
    &__types {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    &__type {
      padding: .5em;
    }
    &__scroll{
      height: 100%;
      overflow: auto;
    }
    @media screen and (max-width: 768px) {
      & {
        flex-direction: column;
        &__types {
          position: fixed;
          flex-direction: row;
          z-index: 1;
          padding: .5em;
          width: 100%;
          font-size: 1em;
          flex-wrap: wrap;
        }

        &__inner {
          position: relative;
          top: 3.5em;
        }
        &__scroll {
          height: calc(100vh - 7em);
        }
      }
    }
  }
</style>
<script>
  import FeedVideoList from '@/components/feed/FeedVideoList';
  import Comment from '@/components/comments/Comment';
  export default {
    middleware: 'auth',
    head() {
      return {
        title: this.$t('feed._title')
      }
    },
    async mounted() {
      if (this.$route.query.types) {
        let types = this.$route.query.types.split(',');
        Object.keys(this.types).forEach(type=>{
          this.types[type] = types.indexOf(type) !== -1;
        });
      }
      this.reloadFeed();
    },
    watch: {
      types: {
        handler() {
          let types = [];
          Object.keys(this.types).forEach(type=>{
            if (this.types[type]) {
              types.push(type);
            }
          });
          this.$router.push({ path: '/user/feed', query: { types: types.join(',') }});
          this.reloadFeed();
        },
        deep: true
      }
    },
    components: {
      FeedVideoList,
      comment
    },
    computed: {
      feedList() {
        return this.feed.data;
      }
    },
    data() {
      return {
        feed: [],
        loading: false,
        loadingOnScroll: false,
        currentPage: 1,
        types: {
          news: true,
          videos: true,
          posts: true,
        }
      }
    },
    methods: {
      async reloadFeed() {
        this.loading = true;
        this.currentPage = 1;
        this.feed = {
          data: []
        };
        this.loading = true;
        let feed = (await this.$axios.post('feed',{types: this.types})).data.list;
        this.feed = feed;
        this.loading = false;
      },
      loadMore() {
        if (!this.loadingOnScroll) {
          if (this.currentPage < this.feed.last_page) {
            this.currentPage++;
            this.loadingOnScroll = true;
            this.$axios.post('feed', {page: this.currentPage, types: this.types}).then(res => {
              this.feed.data = [...this.feed.data, ...res.data.list.data];
              this.loadingOnScroll = false;
            })
          }
        }
      }
    }
  }
</script>
