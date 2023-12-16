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
      <div class="channel-layout__broadcast-title" v-if="activeBroadcast">
        <c-icon icon="chevron_right" />
        <span class="channel-layout__broadcast-title__text" v-if="!broadcastTitleEditor.visible">{{activeBroadcast.title && activeBroadcast.title.length > 0 ? activeBroadcast.title : $t('channel.live_broadcast')}}</span>
        <c-button transparent icon-only icon="edit" v-if="!broadcastTitleEditor.visible && canEditBroadcastName" @click="showBroadcastTitleEditor()" />
        <div class="channel-layout__broadcast-title__edit-panel" v-if="broadcastTitleEditor.visible">
          <c-input class="channel-layout__broadcast-title__edit-panel__input" v-model="broadcastTitleEditor.data.title" />
          <c-button flat :loading="broadcastTitleEditor.loading" @click="saveBroadcastName()">{{$t('global.ok')}}</c-button>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
.channel-layout {
  &__top-block {
    padding: 1em;
    display: flex;
    align-items: center;
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
    margin-right: 1em;
  }

  &__broadcast-title {
    display: flex;
    align-items: center;
    &__edit-panel {
      --vertical-margin: 0;
      display: flex;
      align-items: center;
      &__input {
        margin-right: .5em;
      }
    }
  }
}
</style>
<script>
  import { mapState } from "vuex";

  import SubscribeButton from "@/components/buttons/SubscribeButton";
  import Rating from "@/components/Rating";

  export default {
    components: {Rating, SubscribeButton},
    computed: {
      ...mapState('auth', ['user']),
    },
    data() {
      return {
        activeBroadcast: this.channel.active_broadcast,
        broadcastTitleEditor: {
          data: {
            title: '',
          },
          visible: false,
          loading: false
        },
      }
    },
    methods: {
      saveBroadcastName() {
        this.broadcastTitleEditor.loading = true;
        this.$api.put(`channels/${this.channel.id}`, {
          additional_settings: {
            broadcast: this.broadcastTitleEditor.data
          }
        }).then(({title}) => {
          this.activeBroadcast.title = this.broadcastTitleEditor.data.title;
          this.broadcastTitleEditor.visible = false;
        }).finally(() => {
          this.broadcastTitleEditor.loading = false;
        })
      },
      showBroadcastTitleEditor() {
        this.broadcastTitleEditor.data.name = this.activeBroadcast.title;
        this.broadcastTitleEditor.visible = true;
      },
      canEditBroadcastName() {
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
