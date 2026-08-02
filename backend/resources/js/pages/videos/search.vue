<template>
  <div class="search-page">
    <div class="search-page__params" @keydown="onKeyDown">
      <div class="search-page__main-input-container">
        <c-input icon="search" :title="$t('search.heading')" v-model="searchData.search"/>
      </div>
      <div class="search-page__tags">
        <c-tags-input :title="$t('videos.search.tags')" v-model="searchData.tags"/>
        <div class="search-page__tags__list">
          <c-tag v-for="tag in tags" :key="tag.tag" :count="tag.count" v-if="searchData.tags.indexOf(tag.tag) === -1" @click="searchData.tags.push(tag.tag)" >
            {{tag.tag}}
          </c-tag>
        </div>
      </div>
      <div class="search-page__sort-container">
        <c-select :options="sortOptions" :title="$t('videos.search.sort.heading')" v-model="searchData.order"/>
      </div>
      <div class="search-page__button-container">
        <c-button big @click="loadSearch()">{{$t('videos.search.heading')}}</c-button>
      </div>
    </div>
    <div class="search-page__results">
      <MediaList :title="searchTitle"  :searchParams="searchParams"/>
    </div>
  </div>

</template>
<style lang="scss">

</style>
<script>
  import MediaList from '@/components/MediaList.vue';
  export default {
    computed: {
      searchTitle() {
        if (!this.lastRequest) {
          return this.$t('videos.search.title');
        }
        return this.$t('channels.search.by_request')+' "'+this.lastRequest+'"';
      }
    },
    async asyncData({app}) {
      let tags = (await app.$api.get(`/media/tags?type=video`));
      return {
        tags: tags.data.tags
      };
    },
    methods: {
      onKeyDown(e) {
        if (e.keyCode === 13) {
          this.loadSearch();
        }
      },
      loadSearch() {
        let esc = encodeURIComponent;
        this.searchParams = Object.keys(this.searchData)
          .map(k => esc(k) + '=' + esc(this.searchData[k]))
          .join('&');

        this.lastRequest = this.searchData.search;
        this.$router.push({path: this.$route.path, query: Object.assign({}, this.$route.query, {sort: this.searchData.order, q: this.searchData.search, tags: this.searchData.tags.join(",")})});
      }
    },
    head() {
      return {
        title: this.$t('videos.search.page_title')
      }
    },
    mounted() {

    },
    data() {
      return {
        sortOptions: [
          {
            name: this.$t('media.search.sort.new'),
            value: 'new'
          },
          {
            name: this.$t('media.search.sort.most_watched'),
            value: 'most_watched'
          },
          {
            name: this.$t('media.search.sort.best'),
            value: 'best'
          },
          {
            name: this.$t('media.search.sort.old'),
            value: 'old'
          }
        ],
        searchParams: this.$route.query.q ? 'search='+ this.$route.query.q : "",
        searchData: {
          search: this.$route.query.q || "",
          tags: this.$route.query.tags ? this.$route.query.tags.split(",") : [],
          order: this.$route.query.sort || 'new',
        },
        lastRequest: this.$route.query.q || ""
      }
    },
    components: {
      MediaList
    },
  }
</script>
