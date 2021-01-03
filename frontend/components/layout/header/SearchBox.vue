<template>
  <div class="search-box">
    <m-input @submit="onSubmit" :title="$t('search.input_title')" icon="search" v-model="searchText" />
  </div>
</template>
<style lang="scss">
  .search-box {
    flex: 1;
    .input__container {
      margin: -.5em 0 0;
    }
  }
</style>
<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'

  @Component({})
  export default class SearchBox extends Vue {

    searchText: string = '';

    onSubmit(e: Event) {
      if (this.searchText.length > 0) {
        this.$router.push(`/search?q=${encodeURI(this.searchText)}`)
      }
    }
  }
</script>
