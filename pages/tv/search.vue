<template>
  <layout-with-left-menu class="search-page">
    <template slot="tabs">
      <div class="search-page__params">
        <c-input icon="search" :title="$t('search.heading')" v-model="searchData.search"/>
        <div class="search-page__tags">
          <c-tags-input :title="$t('channels.search.tags')" v-model="searchData.tags"/>
          <div class="search-page__tags__list">
            <c-tag v-for="tag in tags" :key="tag.tag" :count="tag.count" v-if="searchData.tags.indexOf(tag.tag) === -1" @click.native="searchData.tags.push(tag.tag)" >
              {{tag.tag}}
            </c-tag>
          </div>
        </div>
       <!-- <c-checkbox :title="$t('channels.search.online')" v-model="searchData.online"/> -->
        <c-select :options="sortOptions" :title="$t('channels.search.sort.heading')" v-model="searchData.order"/>
        <c-button big @click="loadSearch()">{{$t('channels.search.heading')}}</c-button>
      </div>

    </template>
    <template slot="main">
      <channels-list v-if="initialized" :title="searchTitle" :queryParams="queryParams"/>
    </template>
  </layout-with-left-menu>

</template>
<style lang="scss">

</style>
<script>
  import ChannelsList from '@/components/ChannelsList';
  import LayoutWithLeftMenu from "@/components/LayoutWithLeftMenu";
  export default {
    computed: {
      searchTitle() {
        if (!this.lastRequest) {
          return this.$t('channels.search.heading');
        }
        return this.$t('channels.search.by_request')+' "'+this.lastRequest+'"';
      }
    },
    async asyncData({app}) {
      let tags = await app.$api.get(`/channels/tags?type=tv`);
      return {
        tags
      };
    },
    mounted() {
      this.setSearchParams();
      this.initialized = true;
    },
    methods: {
      onKeyDown(e) {
        if (e.keyCode === 13) {
          this.loadSearch();
        }
      },
      setSearchParams() {
        let esc = encodeURIComponent;
        this.searchParams = Object.keys(this.searchData)
          .filter(k => this.searchData[k] !== '')
          .map(k => esc(k) + '=' + esc(this.searchData[k]))
          .join('&');
        console.log(this.searchData, this.searchParams);
      },
      loadSearch() {
        this.setSearchParams();
        this.lastRequest = this.searchData.search;
        this.$router.push({path: this.$route.path, query: Object.assign(
          {},
            this.$route.query,
            {sort: this.searchData.order,
              q: this.searchData.search !== '' ? this.searchData.search : undefined,
              tags: this.searchData.tags.length > 0 ? this.searchData.tags.join(",") : undefined,
              online: this.searchData.online ? 'true' : undefined
            }
          )});
      }
    },
    head() {
      return {
        title: this.$t('channels.search.page_title')
      }
    },
    data() {
      return {
        sortOptions: [
          {
            name: this.$t('channels.search.sort.new'),
            value: 'created_at'
          },
          {
            name: this.$t('channels.search.sort.popular'),
            value: 'views_count'
          },
          {
            name: this.$t('channels.search.sort.best'),
            value: 'likes_count'
          },
          {
            name: this.$t('channels.search.sort.old'),
            value: 'created_at_asc'
          }
        ],
        searchParams: null,
        searchData: {
          search: this.$route.query.q || '',
          tags: this.$route.query.tags ? this.$route.query.tags.split(",") : [],
          online: this.$route.query.online === 'true' ? true : '',
          order: this.$route.query.sort || 'new',
          type: 'tv',
        },
        lastRequest: this.$route.query.q || '',
        initialized: false,
      }
    },
    components: {
      LayoutWithLeftMenu,
      ChannelsList
    },
  }
</script>
