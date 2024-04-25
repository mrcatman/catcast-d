<template>
  <channels-list :searchParams="searchParams">
    <div class="channels-list__header">
      {{$t('channels.search_by_tags')}}
      <c-tag  :key="$index" v-for="(tag, $index) in tagsList">{{tag}}</c-tag>
    </div>
  </channels-list>
</template>
<style lang="scss">

</style>
<script>
  import ChannelsList from '@/components/ChannelsList';
  export default {
    head() {
      return {
        title: this.$t('radio.search_by_tags')+this.tagsList.join(", ")
      }
    },
    computed: {
      tagsList() {
        return this.$route.query.tags.split(',');
      },
      searchParams() {
        let tags = this.$route.query.tags;
        return "tags="+tags+"&order=popularity&type=radio";
      }
    },
    components: {
      ChannelsList
    },
  }
</script>
