<template>
  <c-button v-if="me" flat icon-only icon="settings">
    <c-popup-menu activate-on-parent-click position="bottom-left">
      <c-popup-menu-item v-if="me.is_guest" @click="changeGuestUsername()">{{$t('chat.menu.change_guest_username')}}</c-popup-menu-item>
      <c-popup-menu-item @click="$emit('toggleServiceMessages')">{{hideServiceMessages ? $t('chat.menu.show_service_messages') : $t('chat.menu.hide_service_messages')}}</c-popup-menu-item>
      <c-popup-menu-item v-if="isTeamMember" @click="clearChat()">{{$t('chat.menu.clear_chat')}}</c-popup-menu-item>
      <c-popup-menu-item v-if="isTeamMember" @click="changeMOTD()">{{$t('chat.menu.change_motd')}}</c-popup-menu-item>
      <c-popup-menu-item v-if="isTeamMember && !config.disabled" @click="chatAction({'disabled': true})">{{$t('chat.menu.disable_chat')}}</c-popup-menu-item>
      <c-popup-menu-item v-if="isTeamMember && config.disabled" @click="chatAction({'disabled': false})">{{$t('chat.menu.enable_chat')}}</c-popup-menu-item>
      <c-popup-menu-item v-if="isTeamMember && !config.allow_guests" @click="chatAction({'allow_guests': true})">{{$t('chat.menu.allow_guests')}}</c-popup-menu-item>
      <c-popup-menu-item v-if="isTeamMember && config.allow_guests" @click="chatAction({'allow_guests': false})">{{$t('chat.menu.disallow_guests')}}</c-popup-menu-item>
    </c-popup-menu>
  </c-button>
</template>
<script>
import ModalPrompt from "@/components/modals/ModalPrompt.vue";

export default {
  methods: {
    changeMOTD() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        buttonColor: '',
        buttonText: this.$t('global.save'),
        title: this.$t('chat.menu.change_motd'),
        component: ModalPrompt,
        props: {
          title: this.$t('chat.new_motd'),
          inputName: 'motd'
        },
        formValues: {
          motd: this.config?.motd,
        },
        fn: async ({motd}) => this.chatAction({motd}),
      })
    },
    changeGuestUsername() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        buttonColor: '',
        buttonText: this.$t('global.save'),
        title: this.$t('chat.menu.change_guest_username'),
        component: ModalPrompt,
        props: {
          title: this.$t('chat.new_guest_username'),
          inputName: 'username',
        },
        formValues: {
          username: this.guestUsername,
        },
        fn: async ({username}) => {
          await this.$api.post(`/chat/${this.channel.id}/username`, {
            username
          });
          this.$emit('changeGuestUsername', {username});
        },
      })
    },
    clearChat() {
      this.$api.post(`/chat/${this.channel.id}/clear`);
    },
    chatAction(data) {
      this.$api.put(`/channels/${this.channel.id}`, {
        additional_settings: {
          chat: data
        }
      });
    },
  },
  props: {
    channel: {
      type: Object,
      required: true
    },
    config: {
      type: Object,
      required: true
    },
    me: {
      type: Object,
      required: true
    },
    isTeamMember: {
      type: Boolean,
      required: true
    },
    hideServiceMessages: {
      type: Boolean,
      required: true
    },
    guestUsername: String
  }
}
</script>
