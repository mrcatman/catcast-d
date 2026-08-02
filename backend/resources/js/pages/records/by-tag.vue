<template>
  <RecordsList :searchParams="searchParams">
    <div class="channels-list__header">
      {{$t('records.search_by_tags')}}
      <c-tag  :key="$index" v-for="(tag, $index) in tagsList">{{tag}}</c-tag>
    </div>
  </RecordsList>
</template>
<style lang="scss">

</style>
<script>
  import RecordsList from '@/components/RecordsList';
  export default {
    head() {
      return {
        title: this.$t('records.search_by_tags')+this.tagsList.join(", ")
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
      RecordsList
    },
  }
</script>
