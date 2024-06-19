<template>

  <c-box no-padding>
    <template slot="main">
      <c-thumbs-list ref="list" :config="listConfig">
        <template slot="before_filters">
          <c-button color="green" icon="fa-plus" @click="createNewBroadcast()">{{ $t('dashboard.broadcast.create') }}
          </c-button>
        </template>
        <template slot="after_heading">
          <c-tabs v-model="type" :data="types"/>
        </template>
        <template slot="item" slot-scope="props">
          <broadcast-thumb :data="props.item" @reload="$refs.list.reload()" :dashboard="true"/>
        </template>
      </c-thumbs-list>
    </template>
  </c-box>

</template>
<style lang="scss">

</style>
<script>
import NotificationItem from "@/components/layout/notifications/NotificationItem.vue";
import BroadcastMetadataEditor from "@/components/dashboard/broadcast/BroadcastMetadataEditor.vue";
import ActiveBroadcastDisplay from "@/components/channel/ActiveBroadcastDisplay.vue";
import BroadcastThumb from "@/components/thumbs/BroadcastThumb.vue";

export default {
  head() {
    return {
      title: this.$t('dashboard.broadcast.list')
    }
  },
  computed: {
    types() {
      return [
        {id: 'all', name: this.$t('dashboard.broadcast.types.all')},
        {id: 'planned', name: this.$t('dashboard.broadcast.types.planned')},
        {id: 'finished', name: this.$t('dashboard.broadcast.types.finished')},
      ]
    },
    listConfig() {
      return {
        title: this.$t('dashboard.broadcast.list'),
        url: `channels/${this.channel.id}/broadcasts?show=${this.type}`,
        view: 'list',
        paginate: true,
        infiniteScroll: true,
        innerScroll: true,
        noPadding: true,
        search: true,
        usePreloadingListItem: true,
      }
    }
  },
  async asyncData({app, params}) {
    const key = await app.$api.get(`/channels/${params.id}/stream/key`);
    const servers = await app.$api.get(`/channels/${params.id}/stream/servers`);
    const activeBroadcast = await app.$api.get(`/channels/${params.id}/broadcasts/active`);
    return {key, servers, activeBroadcast};
  },
  components: {
    BroadcastThumb,
    ActiveBroadcastDisplay,
    NotificationItem,
  },
  props: {
    channel: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      reloading: false,
      type: 'all',
    }
  },
  methods: {
    createNewBroadcast() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        component: BroadcastMetadataEditor,
        buttonColor: '',
        buttonText: this.$t('global.save'),
        title: this.$t('dashboard.broadcast.create'),
        props: {planned: true},
        formValues: {
          tags: []
        },
        fn: async (broadcast) => {
          await this.$api.post(`broadcasts`, {
            ...broadcast,
            channel_id: this.channel.id
          });
          this.$refs.list.reload();
        },
      })
    },
  }
}
</script>
