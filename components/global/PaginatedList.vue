<template>
  <div class="paginated-list">
    <c-preloader block v-if="loadingInitial" />
    <c-infinite-scroll :loading="loading && !loadingInitial" @scroll="loadMore()">
      <c-list>
        <slot name="before"></slot>
        <slot name="item" v-for="item in items.data" :item="item"></slot>
        <slot name="after"></slot>
      </c-list>
    </c-infinite-scroll>
    <c-nothing-found small v-if="items.total === 0"/>
    <div class="paginated-list__pager-container"  v-if="items.last_page > 1">
      <c-pager class="paginated-list__pager" :data="items" v-model="page" />
    </div>

  </div>
</template>
<style lang="scss">
.paginated-list {
  position: relative;
  min-height: 100%;
  &__pager-container {
    z-index: 10;
    position: fixed;
    bottom: 1em;
    left: calc(50% - 5em);
    width: 10em;
    text-align: center;
  }
  &__pager {
    display: inline-flex;
    position: sticky;
    top: 2em;
    right: 1em;
  }

}
</style>
<script>
export default {
  computed: {
    url() {
      return `${this.config.url}${this.config.url.indexOf('?') !== -1 ? '&' : '?'}page=${this.page}`;
    }
  },
  watch: {
    config(newConfig, oldConfig) {
      if (newConfig.url !== oldConfig.url) {
        this.reload();
      }
    },
  },
  mounted() {
    this.load();
  },
  methods: {
    updateItemIfExists(updatedItem) {
      console.log('update item', updatedItem);
      this.items.data = this.items.data.map(item => {
        if (item.id === updatedItem.id) {
          return updatedItem;
        }
        return item;
      })
    },
    reload() {
      this.loadingInitial = true;
      this.page = 1;
      this.load();
    },
    async load() {
      this.loading = true;
      console.log('load from load', this.url);
      this.items = await this.$api.get(this.url);
      this.loading = false;
      this.loadingInitial = false;
    },
    async loadMore() {
      if (!this.loading && this.page < this.items.last_page) {
        this.page++;
        this.loading = true;
        let items = await this.$api.get(this.url);
        this.items.data = [...this.items.data, ...items.data];
        this.loading = false;
      }
    }
  },
  data() {
    return {
      items: {},
      loading: false,
      loadingInitial: false,
      page: 1,
    }
  },
  props: {
    config: {
      type: Object,
      required: true
    }
  }
}
</script>
