<template>
  <channels-list :queryParams="queryParams" :title="title" />
</template>
<style lang="scss">

</style>
<script>
  import ChannelsList from '@/components/ChannelsList';
  export default {
    head() {
      return {
        title: this.title
      }
    },
    computed: {
      title() {
        return this.$t('channels.search_by_tag', {tag: this.tag});
      },
      tag() {
        return this.$route.query.tag || '';
      },
      queryParams() {
        return `tags=${this.tag}&order=views&type=tv`;
      }
    },
    components: {
      ChannelsList
    },
  }
</script>
