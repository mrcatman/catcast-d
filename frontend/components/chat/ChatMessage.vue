<template>
  <div class="chat__message" :class="{'chat__message--service': message.is_service}" >
    <span :title="getFullDate(message, true)" class="chat__message__date">{{getDate(message)}}</span>
    <span @click="$emit('reply', message)" class="chat__message__username" :style="{color: message.color}">{{message.username}}</span>
    <span v-if="!message.is_deleted" class="chat__message__text">
      <span class="chat__message__reply">
        <a v-if="message.reply_to" @click="$emit('replyToUser', user)" :style="{color: user.color}" class="chat__message__reply__item" :key="$index" v-for="(user, $index) in message.reply_to">
          {{user.username}},
        </a>
      </span>
      <span class="chat__message__text__content" v-html="message.text"></span>
    </span>
    <span v-else class="chat__message__text chat__message__text--deleted">{{$t('chat.message_deleted')}}</span>

    <span class="chat__message__actions">
      <c-button transparent narrow icon-only icon="menu" >
        <c-popup-menu position="bottom-left" activate-on-parent-click @open="onMenuOpen">
          <c-popup-menu-item  @click="deleteMessage(message)"  v-if="!message.is_deleted && !message.is_service && canDeleteMessage(message)" >
            {{ $t('chat.delete_message') }}
          </c-popup-menu-item>
          <c-popup-menu-item v-if="message.user_id" :to="`/users/${message.user_id}`" target="_blank">
            {{ $t('chat.show_profile') }}
          </c-popup-menu-item>
          <c-popup-menu-item v-if="isTeamMember && messageUserBanState && messageUserBanState.can_change_ban_state && !messageUserBanState.is_banned" @click="changeBanState(true)">{{$t('chat.ban')}}</c-popup-menu-item>
          <c-popup-menu-item v-if="isTeamMember && messageUserBanState && messageUserBanState.can_change_ban_state && messageUserBanState.is_banned" @click="changeBanState(false)">{{$t('chat.unban')}}</c-popup-menu-item>
        </c-popup-menu>
      </c-button>
    </span>
  </div>
</template>
<style lang="scss" scoped>
.chat {
  &__message {
    padding: .5em;
    position: relative;
    clear: both;
    &__actions {
      margin: -.5em 0;
      float: right;
      .button__icon {
        opacity: .2;
      }
      &:hover .button__icon {
        opacity: 1;
      }
    }
    &__text {
      &__content {
        line-height: 1.4;
        word-break: break-all;
      }

      &--deleted {
        font-style: italic;
      }
    }
    &__reply {
      &__item {
        font-weight: bold;
        cursor: pointer;
      }
    }

    &:nth-child(2n) {
      background: var(--darken-1);
    }

    &--service {
      background: var(--darken-2);
    }
    &--service &__text {
      font-style: italic;
    }

    &__date {
      font-weight: 500;
      font-size: .875em;
      margin: 0 .25em 0 0;
    }

    &__username {
      cursor: pointer;
      font-weight: 600;
    }


    .smiley {
      width: 2em !important;
      margin-bottom: -.5em;
    }
  }
}
</style>
<script>
import {format} from "date-fns";

export default {
  data() {
    return {
      messageUserBanState: null
    }
  },
  methods: {
    onMenuOpen() {
      if (this.isTeamMember) {
        this.$api.get(`/chat/${this.message.channel_id}/messages/${this.message.id}/ban-state`).then(data => {
          this.messageUserBanState = data;
        })
      }
    },
    changeBanState(state) {
      this.$api.post(`/chat/${this.message.channel_id}/messages/${this.message.id}/ban-state`, {state}).then(data => {
        this.messageUserBanState.is_banned = data.state;
      })
    },
    getDate(message) {
      return format(new Date(message.created_at), 'HH:mm');
    },
    getFullDate(message) {
      return format(new Date(message.created_at), 'D.M.YYYY HH:mm:ss');
    },
    deleteMessage(message) {
      this.$api.delete(`/chat/messages/${message.id}`);
    },
    canDeleteMessage(message) {
      return this.isTeamMember || (this.me && this.me.id === message.user_id);
    },
  },
  props: {
    message: {
      type: Object,
      required: true,
    },
    isTeamMember: {
      type: Boolean,
      required: true
    },
    me: {
      type: Object,
      required: false,
    }
  }
}
</script>
