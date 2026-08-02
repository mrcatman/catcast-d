<template>
  <div class="page-container">
   <c-thumbs-list :config="listConfig">
     <template #filters slot-scope="props">
       <c-select v-model="props.filters.type" :options="typeOptions" />
     </template>
      <template #item slot-scope="props">
        <channel-thumb :data="props.item" />
      </template>
     <template #after_heading slot-scope="props">
       <c-checkbox :title="$t('channels.online')" v-model="props.filters.online"/>
       <c-tags-input v-model="props.filters.tags" :title="$t('global.tags')" />
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
            online: true
          },
          queryStringFilters: ['type', 'online']
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
