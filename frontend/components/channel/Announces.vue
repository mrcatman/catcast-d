<template>
  <c-thumbs-list ref="list" :config="listConfig">
    <template slot="item" slot-scope="props">
      <broadcast-thumb :data="props.item"  />
    </template>
  </c-thumbs-list>
</template>

<script>
  import AnnounceSubscribeButton from '@/components/buttons/AnnounceSubscribeButton';
  import BroadcastThumb from "@/components/thumbs/BroadcastThumb.vue";
  export default {
    computed: {
      listConfig() {
        return {
          url: `channels/${this.channel.id}/broadcasts?type=planned`,
          view: 'list',
          paginate: true,
          infiniteScroll: true,
          search: true,
          usePreloadingListItem: true,
        }
      }
    },
    components: {
      BroadcastThumb,
      AnnounceSubscribeButton
    },
    props: {
      channel: {
        type: Object,
        required: true,
      }
    },

  }
</script>

