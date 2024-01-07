<template>
  <div class="active-broadcast">
    <c-tag class="active-broadcast__tag" :color="!activeBroadcast.is_online ? 'transparent' : ''">{{broadcast.is_online ? $t('channel.broadcast.online') : $t('channel.broadcast.offline')}}</c-tag>
    <div class="active-broadcast__main">
       <h3 class="active-broadcast__title" v-if="activeBroadcast.is_online">{{activeBroadcast.title}}</h3>
      <c-tag v-if="activeBroadcast.category && activeBroadcast.is_online" class="active-broadcast__category" :to="`/tv/search?category_id=${activeBroadcast.category.id}`">{{activeBroadcast.category.name}}</c-tag>
      <c-button flat v-if="canEdit" icon="settings" @click="editActiveBroadcast()">{{$t('dashboard.broadcast.edit_current')}}</c-button>
    </div>
    <div class="active-broadcast__description" v-if="activeBroadcast.is_online && activeBroadcast.description && activeBroadcast.description.length" v-html="activeBroadcast.display_description"></div>
  </div>
</template>
<style lang="scss" scoped>
.active-broadcast {
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
    margin: 0;
    font-size: 1.125em;
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
    canEdit: Boolean
  }
}
</script>
