<template>
  <div class="records-section">
    <c-preloader block  v-if="loadingInitial" />
    <div class="records-section__header">
      <div class="records-section__search">
        <c-input @change="reload()" :loading="search.loading" v-model="search.text" :inlinePlaceholder="true" icon="search"></c-input>
      </div>
      <div class="records-section__right">
        <div class="records-section__view-change">
          <a @click="currentView = 'grid'" class="records-section__view-change__item" :class="{'records-section__view-change__item--active': currentView === 'grid'}">
            <i class="fa fa-th-large"></i>
          </a>
          <a @click="currentView = 'list'" class="records-section__view-change__item" :class="{'records-section__view-change__item--active': currentView === 'list'}">
            <i class="fa fa-list-ul"></i>
          </a>
        </div>
        <div class="records-section__order">
          <c-select v-model="order" :options="orderOptions" />
        </div>
      </div>
    </div>
    <c-nothing-found v-if="!loading && records && records.data && records.data.length === 0" :title="$t('global.nothing_found')"/>
    <c-infinite-scroll v-else ref="items" :loading="loading" @scroll="loadMore" class="records-section__items" :class="'records-section__items--'+currentView">
      <RecordItem :data="item" :key="$index" v-for="(item, $index) in records.data"/>
    </c-infinite-scroll>
  </div>
</template>
<script>
  import RecordItem from '@/components/RecordItem';
  const count = 24;
  export default {
    components: {
      RecordItem
    },
    watch: {
      currentView(newView) {
        localStorage.records_current_view = newView;
      },
      order() {
        this.reload();
      }
    },
    computed: {
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
      let records = (await this.$api.get(this.url)).data.list;
      this.records = records;
      this.loadingInitial = false;
      this.$parent.$on('scrollBottom', () => {
        this.loadMore();
      })
    },
    data() {
      return {
        loading: false,
        loadingInitial: true,
        records: {},
        currentPage: 1,
        search: {
          loading: false,
          text: ''
        },
        order: 'new',
        currentView: localStorage.records_current_view || 'grid',
      }
    },
    methods: {
      async reload() {
        this.currentPage = 1;
        this.loadingInitial = true;
        let records = (await this.$api.get(this.url)).data.list;
        this.records = records;
        this.loadingInitial = false;
      },
      loadMore() {
        if (!this.loading) {
          if (this.currentPage < this.records.last_page) {
            this.currentPage++;
            this.loading = true;
            this.$axios.get(this.url).then(res => {
              this.records.data = [...this.records.data, ...res.data.data.list];
              this.loading = false;
            })
          }
        }
      }
    },
    props: {
      searchParams: {
        type: String,
        required: true,
      }
    }
  }
</script>
<style lang="scss">
  .records-section {
    width: 100%;
    max-height: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    &__items {
      overflow: auto;
      max-height: calc(100% - 3.5em);
      display: flex;
      flex-wrap: wrap;
    }


    &__items--list &__item {
      width: 100% !important;
      padding-top: 0 !important;
      height: 5em !important;
    }
    &__items--list .video-item {
      &__inner {
        flex-direction: row;
        align-items: center;
        position: relative;
        margin: .25em;
        height: 100%;
      }

      &__picture-container {
        flex: unset;
        max-height: 100%;
        width: 8em!important;
        height: 5em;
      }

      &__title {
        max-width: none!important;
        flex: 1;
      }

      &__info {
        font-size: 1.25em;
        background: none;
        padding: 0;
        margin: 0 0 0 1em;
        text-align: left;
      }

    }

    &__header {
      z-index: 1;
      background: var(--title-box-color);
      padding: .5em;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    &__search .input__container {
      margin: 0;
    }
    &__right {
      display: flex;
      align-items: center;
    }

    &__view-change__item {
      cursor: pointer;
      border-radius: var(--border-radius);
      padding: .5em .75em;
      margin: 0 .25em;
      &--active {
        background: rgba(255, 255, 255, .125);
      }
    }
    &__order {
      position: relative;
      z-index: 1000000;
    }
    .channel-layout__bottom__inner & {
      height: calc(100% - 3em);
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
          padding-top: 70%;
        }
      }
    }
  }
</style>
