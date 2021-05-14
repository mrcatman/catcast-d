<template>
  <div class="table">
    <div class="table__search">
      <m-input icon="search" v-model="search" :title="$t('search.input_title')" />
    </div>

    <table class="table__el">
      <thead>
      <tr>
        <td v-for="column in config.columns" :key="column.prop">{{column.name}}</td>
        <td></td>
      </tr>
      </thead>
      <tbody>
      <tr class="table__row" :class="config.getClasses ? config.getClasses(item) : ''" v-for="item in items.list" :key="item.id">
        <td class="table__col" :class="'table__col--' + column.prop" v-for="column in config.columns" :key="item.id + '_' + column.prop">
          <component :is="column.link ? 'a' : 'span'" :href="column.link ? column.link(item) : undefined">{{column.fn ? column.fn(item) : item[column.prop]}}</component>
        </td>
        <td class="table__col table__col--buttons">
          <div class="table__buttons">
            <slot  name="after_row" v-bind:item="item"></slot>
          </div>

        </td>
      </tr>
      </tbody>
    </table>
    <m-pager v-model="currentPage" :data="items" />

  </div>

</template>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { Prop, Watch } from 'vue-property-decorator'

import ChannelThumb from '~/components/layout/thumbs/Channel.vue'
import Paginator from '~/types/Paginator'

@Component({
  components: {
    ChannelThumb,
  },
})
export default class Table extends Vue {
  @Prop({required: true}) readonly fn!: Function;
  @Prop({required: true}) readonly config!: any;

  items = {} as Paginator<any>;
  currentPage: number = 1;
  search: string = '';
  searchTimeout: any = null;

  @Watch('currentPage')
  async onPageChange(page: number) {
    this.items = await this.fn({page, search: this.search});
  }

  @Watch('search')
  async onSearchStringChange(search: string) {
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(async() => {
      this.currentPage = 1;
      this.items = await this.fn({page: 1, search});
    }, 500)

  }

  @Watch('fn')
  async onFunctionChange() {
    this.currentPage = 1;
    this.items = await this.fn({ page: 1, search: this.search });
  }

  async fetch() {
    this.items = await this.fn({ page: 1 });
  }
}
</script>
