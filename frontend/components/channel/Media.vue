<template>
  <div class="channel-layout__media">
    <media-list :url="`/channels/${channel.id}/media?order=${order}`" :config="{search: true}" >
      <template slot="filters">
        <c-select :options="orderOptions" v-model="order" />
      </template>
    </media-list>
  </div>
</template>
<style lang="scss" scoped>
  .channel-layout {
    &__media {
      width: 100%;
      height: 100%;


      ::v-deep .media-thumb__channel-logo-and-name {
        display: none;
      }
    }
  }
</style>
<script>
  import MediaList from '@/components/MediaList.vue';
  export default {
    data() {
      return {
        order: 'new',
        orderOptions: [
          {
            name: this.$t('media.search.sort.new'),
            value: 'new'
          },
          {
            name: this.$t('media.search.sort.most_watched'),
            value: 'most_watched'
          },
          {
            name: this.$t('media.search.sort.best'),
            value: 'best'
          },
          {
            name: this.$t('media.search.sort.old'),
            value: 'old'
          }
        ],
      }
    },
    components: {
      MediaList
    },
    props: {
      channel: {
        type: Object,
        required: true
      }
    },
    mounted() {
       this.$parent.$on('scrollBottom', () => {
         this.$emit('scrollBottom');
       })
    },
  }
</script>
