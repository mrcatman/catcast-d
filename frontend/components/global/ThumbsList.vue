<template>
  <div class="thumbs-list" ref="list" :class="{'thumbs-list--with-inner-scroll': config.innerScroll}">
    <div class="thumbs-list__heading-container">
      <slot name="before_heading"></slot>
      <c-row align="center" justify="between" class="thumbs-list__heading"
             v-if="config.title || config.search || config.filters || config.canChangeView">
        <c-col auto-width v-if="config.title">
          <component :is="config.expandLink ? 'nuxt-link' : 'span'" :to="config.expandLink"
                     class="thumbs-list__heading__text"
                     :class="{'thumbs-list__heading__text--link': config.expandLink}">
            {{ config.title }}
            <c-icon class="thumbs-list__heading__text__arrow" v-if="config.expandLink" icon="chevron_right"></c-icon>
          </component>
        </c-col>

        <c-col auto-width v-if="$slots.before_filters">
          <slot name="before_filters"></slot>
        </c-col>
        <c-col class="thumbs-list__search" v-if="config.search">
          <c-input @input="reload()" v-model="filters.search" :placeholder="$t('search.placeholder_section')"
                   icon="search" :debounce="true"/>
        </c-col>

        <c-col auto-width>
          <slot name="filters" :filters="filters"></slot>
        </c-col>
        <c-col auto-width v-if="config.canChangeView">
          <change-view class="thumbs-list__change-view" v-model="view"/>
        </c-col>
        <c-col auto-width v-if="$slots.after_filters">
          <slot name="after_filters"></slot>
        </c-col>
      </c-row>
      <slot name="after_heading"></slot>
    </div>

    <div class="thumbs-list__inner" :class="{'thumbs-list__inner--no-padding': config.noPadding}" ref="itemsList">
      <div class="thumbs-list__items-container" :is="config.infiniteScroll ? 'c-infinite-scroll' : 'div'"
           @scroll="loadMore" @scrollToTop="loadPrevious">
        <c-dynamic-row class="thumbs-list__items" :class="viewClasses" :itemWidth="config.itemWidth" ref="dynamic_row">
          <div v-if="!loadedInitial" v-for="$i in lastItemsCount" :key="$i" class="thumbs-list__item">
            <preloading-list-item v-if="config.usePreloadingListItem"/>
            <preloading-thumb v-else/>
          </div>
          <div v-if="$slots.before && loadedInitial" class="thumbs-list__item">
            <slot name="before"></slot>
          </div>
          <div v-if="loadedInitial" :key="'item_' + $index" v-for="(item, $index) in list.data"
               class="thumbs-list__item">
            <slot name="item" :item="item"></slot>
          </div>
          <div v-if="$slots.after && loadedInitial" class="thumbs-list__item">
            <slot name="after"></slot>
          </div>
        </c-dynamic-row>
        <div v-if="config.infiniteScroll && page < list.last_page" class="thumbs-list__load-more">
          <c-button icon="refresh" :loading="loading && loadedInitial" @click="loadMore(true)">{{
              $t('global.load_more')
            }}
          </c-button>
        </div>
        <c-nothing-found small v-if="!loading && list.data.length === 0 && !config.hideNothingFoundBlock"/>
      </div>
    </div>
    <div v-if="$slots.footer" class="thumbs-list__footer">
      <slot name="footer"></slot>
    </div>

    <div v-if="config.paginate && !config.hidePaginator" class="thumbs-list__pager-container">
      <c-pager @pageChange="onPageChange" :data="list" v-model="page"/>
    </div>
  </div>
</template>
<style lang="scss">
.thumbs-list {
  &--with-inner-scroll {
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }

  &--with-pager {
    padding-right: 5em;
    height: 100%;
    overflow: auto;
    @media screen and (max-width: 768px) {
      padding-right: 0;
    }
  }

  &__inner {
    padding: 0 .5em;
    display: flex;
    flex-direction: column;

    &--no-padding {
      padding: 0;
    }
  }

  &--with-inner-scroll &__inner {
    flex: 1;
    overflow: auto;
    overflow-x: hidden;
  }

  &__heading {
    padding: .5em 1em;

    &__text {
      font-size: 1.325em;
      font-weight: 400;
      display: flex;
      align-items: center;

      &--link {
        text-decoration: none;
      }

      &__arrow {
        opacity: .75;
        transition: opacity .25s;
      }

      &:hover &__arrow {
        opacity: 1;
      }
    }
  }

  &--with-inner-scroll &__heading {
    border-bottom: 1px solid var(--input-border-color);
  }

  &__search {
    @media screen and (max-width: 768px) {
      min-width: 50%;
    }
  }

  &__preloader {
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.1);
  }

  &__items {
    margin: 0 -.5em;

    &.view-grid {
      margin: 0;
    }
  }


  &__item {
    position: relative;

    &--auto-height {
      height: auto;
    }
  }

  &__load-more {
    text-align: center;
    padding: 1.75em 0;
  }

  &__pager-container {
    z-index: 10;
    position: fixed;
    bottom: 1em;
    right: 1em;
    width: 10em;
    text-align: center;

    .pager {
      display: inline-flex;
    }
  }
}

.modal .thumbs-list--with-inner-scroll {
  max-height: 72.5vh;
}

.view-list {
  margin: 0;

  .thumbs-list {
    &__item {
      width: 100% !important;
    }
  }
}
</style>
<script>
import PreloadingThumb from "@/components/preloading/PreloadingThumb";
import PreloadingListItem from "@/components/preloading/PreloadingListItem";
import ChangeView from "@/components/ChangeView";

import isMobile from "@/helpers/isMobile";
import removeEmpty from '@/helpers/object/removeEmpty';
import pick from "@/helpers/object/pick";

export default {
  props: {
    data: Object,
    config: Object,
  },
  data() {
    return {
      loading: true,
      loadedInitial: false,
      loadMoreError: false,
      list: {},
      view: localStorage.current_view || 'grid',
      page: 1,
      loadedFromPage: 1,
      lastItemsCount: 24,
      filters: {
        search: '',
        ...(this.config.filters ? this.config.filters : {}),
        ...(this.config.queryStringFilters ? pick(this.$route.query, this.config.queryStringFilters) : {})
      },
    }
  },
  watch: {
    config(newConfig, oldConfig) {
      this.onConfigChange();
      if (newConfig.url !== oldConfig.url) {
        this.reload();
      }
    },
    $route(newRoute, oldRoute) {
      if (!this.config.queryStringFilters) {
        return;
      }
      const updatedFilters = {};
      for (const key in newRoute.query) {
        if (this.config.queryStringFilters.includes(key) && newRoute.query[key] !== oldRoute.query[key]) {
          updatedFilters[key] = newRoute.query[key];
        }
      }
      this.filters = {
        ...this.filters,
        ...updatedFilters
      }
    },
    filters: {
      handler() {
        this.reload();
      },
      deep: true
    },
    view(newView) {
      localStorage.current_view = newView;
    },
    page(newPage) {
      if (!this.config.disableQuerystringUpdate) {
        this.$router.push({query: {...this.$route.query, page: newPage}});
      }
    }
  },
  computed: {
    viewClasses() {
      const currentView = this.config.view ? this.config.view : this.view;
      const splitted = currentView.split('-');
      if (splitted.length === 1) {
        return [`view-${currentView}`];
      } else {
        if (splitted.length === 2) {
          return [`view-${splitted[0]}`, `view-${splitted[0]}-${splitted[1]}`];
        }
      }
    },
    empty() {
      return !this.loading && this.list.total === 0 && !this.filters.search.length;
    }
  },
  async mounted() {
    this.onConfigChange();
    if (this.data) {
      this.list = this.data;
      this.page = this.data.current_page;
      this.loadedFromPage = this.data.current_page;
      this.loading = false;
    } else {
      await this.load();
    }
    this.loadedInitial = true;
  },
  methods: {
    params(page = this.page) {
      return removeEmpty({
        page,
        ...this.filters,
      })
    },
    url(page = this.page) {
      const params = new URLSearchParams(this.params(page));

      const rowsToLoad = this.config.rowsToLoad || 5; // todo: get from height
      const itemsToLoad = rowsToLoad * this.$refs.dynamic_row.itemsInRow;
      params.append('count', itemsToLoad);

      return `${this.config.url}${this.config.url.indexOf('?') === -1 ? '?' : '&'}${params.toString()}`;
    },
    setQueryParams() {
      if (this.config.queryStringFilters) {
        const newQuery = {
          ...this.$route.query,
          ...pick(this.params(this.page), this.config.queryStringFilters)
        };
        if (JSON.stringify(newQuery) !== JSON.stringify(this.$route.query)) {
          this.$router.replace({
            path: this.$route.path,
            query: newQuery
          })
        }
      }
    },
    onConfigChange() {
      if (this.config.innerScroll && !isMobile()) {
        const margin = 36;
        const top = this.$refs.list.getBoundingClientRect().top;
        this.$refs.list.style.height = `${window.innerHeight - top - margin}px`;
      } else {
        this.$refs.list.style.height = '';
      }
    },
    updateItemIfExists(updatedItem) {
      this.list.data = this.list.data.map(item => {
        if (item.id === updatedItem.id) {
          return updatedItem;
        }
        return item;
      })
    },
    onPageChange(page) {
      this.loadedInitial = false;
      this.page = page;
      this.list = {};
      this.load();
    },
    reload() {
      this.loadedInitial = false;
      this.page = 1;
      this.loadedFromPage = 1;
      this.loading = true;
      this.load();
    },
    async load() {
      this.loading = true;
      this.list = await this.$api.get(this.url());
      this.lastItemsCount = this.list.data.length > 0 ? this.list.data.length : (this.$refs.dynamic_row ? this.$refs.dynamic_row.itemsInRow : 1);
      this.setQueryParams();
      this.loadedInitial = true;
      this.loading = false;
    },
    async loadPrevious() {
      if (!this.loading && this.loadedFromPage > 1) {
        this.loading = true;
        this.loadedFromPage--;
        try {
          const data = (await this.$api.get(this.url(this.loadedFromPage), {})).data;
          this.setQueryParams();
          this.list.data = [...data, ...this.list.data];
          this.loading = false;
        } catch (e) {
          this.loadMoreError = true;
        } finally {
          this.loading = false;
        }
      }
    },
    async loadMore(force = false) {
      if (!this.loading && this.page < this.list.last_page && (!this.loadMoreError || force)) {

        this.loadMoreError = false;
        this.loading = true;
        try {
          const data = (await this.$api.get(this.url(this.page + 1), {})).data;
          this.page++;
          this.setQueryParams();
          this.list.data = [...this.list.data, ...data];
        } catch (e) {
          this.loadMoreError = true;
        } finally {
          this.loading = false;
        }
      }
    }
  },
  components: {
    ChangeView,
    PreloadingThumb,
    PreloadingListItem
  }
}
</script>
