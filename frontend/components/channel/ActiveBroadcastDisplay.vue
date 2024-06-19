<template>
  <div class="active-broadcast">
    <div class="active-broadcast__top">
      <c-tag class="active-broadcast__tag" :color="!activeBroadcast.is_online ? 'transparent' : ''">{{broadcast.is_online ? $t('channel.broadcast.online') : $t('channel.broadcast.offline')}}</c-tag>
      <c-button flat icon-only v-if="activeBroadcast.can_edit" icon="settings" @click="editActiveBroadcast()">
        <template slot="tooltip">
          <c-tooltip>{{$t('dashboard.broadcast.edit_current')}}</c-tooltip>
        </template>
      </c-button>
    </div>
    <div v-if="activeBroadcast.is_online" class="active-broadcast__main">
       <h3 class="active-broadcast__title" >{{activeBroadcast.title}}</h3>
       <c-tag v-if="activeBroadcast.category" class="active-broadcast__category" :to="`/tv/search?category_id=${activeBroadcast.category.id}`">{{activeBroadcast.category.name}}</c-tag>
    </div>
    <div class="active-broadcast__description" v-if="activeBroadcast.is_online && activeBroadcast.description && activeBroadcast.description.length" v-html="activeBroadcast.display_description"></div>
  </div>
</template>
<style lang="scss" scoped>
.active-broadcast {
  &__top {
    display: flex;
    align-items: center;
  }
  &__main {
    margin-top: 1em;
    display: flex;
    align-items: center;
  }
  &__category {
    margin: 0 .5em;
  }
  &__title {
    margin: 0;
    font-size: 1.325em;
    font-weight: bold;
  }
  &__description {
    margin-top: 1em;
  }
  &__tag {
    margin: 0 .5em 0 0;
  }

}
</style>
<script>
import BroadcastMetadataEditor from "@/components/dashboard/broadcast/BroadcastMetadataEditor.vue";

export default {
  watch: {
    broadcast(broadcast) {
      this.activeBroadcast = broadcast;
    }
  },
  data() {
    return {
      activeBroadcast: this.broadcast,
    }
  },
  methods: {
    editActiveBroadcast() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        component: BroadcastMetadataEditor,
        buttonColor: '',
        buttonText: this.$t('global.save'),
        title: this.$t('dashboard.broadcast.edit_current'),
        props: {planned: false},
        formValues: {
          ...this.activeBroadcast
        },
        fn: async (broadcast) => {
          await this.$api.put(`/channels/${this.channel.id}/broadcasts/active`, broadcast);
          this.activeBroadcast = await this.$api.get(`/channels/${this.channel.id}/broadcasts/active`);
        },
      })
    },
  },
  props: {
    broadcast: Object,
    channel: Object,
  }
}
</script>
