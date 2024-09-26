<template>
  <div class="page-container">
   <c-thumbs-list :config="listConfig">
     <template slot="filters" slot-scope="props">
       <c-select v-model="props.filters.type" :title="$t('channels.type')" :options="typeOptions" />
     </template>
      <template slot="item" slot-scope="props">
        <channel-thumb :data="props.item" />
      </template>
    </c-thumbs-list>
  </div>
</template>

<script>
  import ChannelThumb from "@/components/thumbs/ChannelThumb";
  export default {
    components: {
      ChannelThumb,
    },
    data() {
      return {
        listConfig: {
          title: this.$t('channels.heading'),
          url: '/channels',
          canChangeView: true,
          paginate: true,
          infiniteScroll: true,
          search: true,
          filters: {
            is_online: true
          },
          queryStringFilters: ['type', 'is_online']
        },
        typeOptions: [
          {name: this.$t('global.all'), value: ''},
          {name: this.$t('channels.tv'), value: 'tv'},
          {name: this.$t('channels.radio'), value: 'radio'}
        ]
      }
    }
  }
</script>
