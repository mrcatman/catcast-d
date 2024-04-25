<template>
  <MediaList :searchParams="searchParams">
    <div class="channels-list__header">
      {{$t('videos.search_by_tags')}}
      <c-tag  :key="$index" v-for="(tag, $index) in tagsList">{{tag}}</c-tag>
    </div>
  </MediaList>
</template>
<style lang="scss">

</style>
<script>
  import MediaList from '@/components/MediaList.vue';
  export default {
    head() {
      return {
        title: this.$t('videos.search_by_tags')+this.tagsList.join(", ")
      }
    },
    computed: {
      tagsList() {
        return this.$route.query.tags.split(',');
      },
      searchParams() {
        let tags = this.$route.query.tags;
        return "tags="+tags+"&order=new";
      }
    },
    components: {
      MediaList
    },
  }
</script>
