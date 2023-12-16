<template>
  <div class="channel-layout__media">
    <media-list :url="`/channels/${channel.id}/media?order=${order}`" :config="{search: true}" >
      <template slot="filters">
        <c-select :options="orderOptions" v-model="order"></c-select>
      </template>
    </media-list>
  </div>
</template>
<style lang="scss">
  .channel-layout{
    &__media {
      width: 100%;
      height: 100%;
      .media-item__inner {
        background: rgba(0, 0, 0, 0.25);
      }
      .media-item__channel {
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
            name: this.$t('videos.search.sort.new'),
            value: 'new'
          },
          {
            name: this.$t('videos.search.sort.most_watched'),
            value: 'most_watched'
          },
          {
            name: this.$t('videos.search.sort.best'),
            value: 'best'
          },
          {
            name: this.$t('videos.search.sort.old'),
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
