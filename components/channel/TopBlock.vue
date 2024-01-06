<template>
  <div class="channel-layout__top-block">
    <div class="channel-layout__logo" v-if="channel.logo" :style="{backgroundImage :'url('+channel.logo+')'}"></div>
    <div class="channel-layout__top-block__texts">
      <div class="channel-layout__title-container">
        <div class="channel-layout__title">
          {{channel.name}}
        </div>
        <div class="buttons-row">
          <rating entity-type="channels" :entity-id="channel.id" />
          <subscribe-button entity-type="channels" :entity-id="channel.id" />
        </div>
      </div>
      <active-broadcast-display :broadcast="activeBroadcast" :can-edit="canEditBroadcastName" />
    </div>
  </div>
</template>
<style lang="scss">
.channel-layout {
  &__top-block {
    padding: 1em;
    display: flex;
    &__texts {
      flex: 1;
    }
  }
  &__logo {
    width: 4em;
    height: 4em;
    background-position: center;
    background-repeat: no-repeat;
    background-size: contain;
    margin-top: 1em;
    margin-right: 1em;
  }

}
</style>
<script>
  import { mapState } from "vuex";

  import SubscribeButton from "@/components/buttons/SubscribeButton";
  import Rating from "@/components/Rating";
  import ActiveBroadcastDisplay from "@/components/channel/ActiveBroadcastDisplay.vue";

  export default {
    components: {ActiveBroadcastDisplay, Rating, SubscribeButton},
    computed: {
      ...mapState('auth', ['user']),
    },
    data() {
      return {
        activeBroadcast: this.channel.active_broadcast,

      }
    },
    methods: {

      canEditBroadcastName() {
        return true; // todo: fix
        if (!this.activeBroadcast || !this.permissions) {
          return false;
        }

        //todo: get 'can edit' permissions from backend

        let list = this.permissions.list;
        let full_list = this.permissions.full_list;

        if (list.indexOf('admin') !== -1 || list.indexOf('owner') !== -1 || list.indexOf('channel_admin') !== -1) {
          return true;
        }
        if (full_list.indexOf('live_broadcast') !== -1) {
          return true;
        }
        if (list.indexOf('live_broadcast') !== -1 && this.activeBroadcast.live_user && activeBroadcast.broadcast.live_user.id === this.user.id) {
          return true;
        }
        return false;
      },
    },
    props: {
      channel: {
        type: Object,
        required: true,
      },
    }
  }
</script>
