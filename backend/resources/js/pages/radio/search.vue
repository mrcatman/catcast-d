<template>
  <div class="search-page">
    <div class="search-page__params" @keydown="onKeyDown">
      <div class="search-page__main-input-container">
        <c-input icon="search" :title="$t('search.heading')" v-model="searchData.search"/>
      </div>
      <div class="search-page__tags">
        <c-tags-input :title="$t('radio.search.tags')" v-model="searchData.tags"/>
        <div class="search-page__tags__list">
          <c-tag v-for="tag in tags" :key="tag.tag" :count="tag.count" v-if="searchData.tags.indexOf(tag.tag) === -1" @click="searchData.tags.push(tag.tag)" >
            {{tag.tag}}
          </c-tag>
        </div>
      </div>
      <div class="search-page__online-checkbox-container">
        <c-checkbox :title="$t('radio.search.online')" v-model="searchData.online"/>
      </div>
      <div class="search-page__sort-container">
        <c-select :options="sortOptions" :title="$t('radio.search.sort.heading')" v-model="searchData.order"/>
      </div>
      <div class="search-page__button-container">
        <c-button big @click="loadSearch()">{{$t('radio.search.heading')}}</c-button>
      </div>
    </div>
    <div class="search-page__results">
      <channels-list :title="searchTitle" :searchParams="searchParams"/>
    </div>
  </div>

</template>
<style lang="scss">

</style>
<script>
  import ChannelsList from '@/components/ChannelsList';
  export default {
    computed: {
      searchTitle() {
        if (!this.lastRequest) {
          return this.$t('radio.search.title');
        }
        return this.$t('channels.search.by_request')+' "'+this.lastRequest+'"';
      }
    },
    async asyncData({app}) {
      let tags = (await app.$api.get(`/channels/tags?type=radio`));
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
        this.$router.push({path: this.$route.path, query: Object.assign({}, this.$route.query, {sort: this.searchData.order, q: this.searchData.search, tags: this.searchData.tags.join(","), online: this.searchData.online})});
      }
    },
    head() {
      return {
        title: this.$t('radio.search.page_title')
      }
    },
    mounted() {

    },
    data() {
      return {
        sortOptions: [
          {
            name: this.$t('radio.search.sort.new'),
            value: 'new'
          },
          {
            name: this.$t('radio.search.sort.popular'),
            value: 'popular'
          },
          {
            name: this.$t('radio.search.sort.best'),
            value: 'best'
          },
          {
            name: this.$t('radio.search.sort.old'),
            value: 'old'
          }
        ],
        searchParams: this.$route.query.q ? 'type=radio&search='+ this.$route.query.q : "type=radio",
        searchData: {
          search: this.$route.query.q || "",
          tags: this.$route.query.tags ? this.$route.query.tags.split(",") : [],
          online: !!this.$route.query.online,
          order: this.$route.query.sort || 'new',
          type: 'radio',
        },
        lastRequest: this.$route.query.q || ""
      }
    },
    components: {
      ChannelsList
    },
  }
</script>
