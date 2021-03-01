<template>
  <div class="chat">
    <div class="chat__users" v-show="showUsersList">
      <div class="chat__users__title">{{$t('chat.users')}}</div>
      <div class="chat__user" v-for="user in users">
        <span class="chat__user__avatar" v-if="user.avatar" :style="{backgroundImage: `url(${user.avatar})`}"></span>
        <span class="chat__user__name">{{user.login}}</span>
      </div>
    </div>
    <div class="chat__messages">
      <div class="chat__message" v-for="message in messages" :key="message.id">
        <span class="chat__message__time">{{ getReadableTime(message.ts) }}</span>
        <span class="chat__message__user">{{ message.author.login }}</span>
        {{message.content}}
      </div>
    </div>
    <div class="chat__input">
      <m-input type="textarea" v-model="form.content" title="" class="chat__input__el" />
      <div class="chat__input__bottom">
        <m-button @click="sendMessage()" :disabled="form.content.length === 0">{{$t('common.send')}}</m-button>
        <div class="chat__controls">
          <a @click="showUsersList = !showUsersList" class="chat__control" :class="{'chat__control--active': showUsersList}">
            <span class="tooltip">{{$t('chat.users')}}</span>
            <i class="material-icons">supervisor_account</i>
            <span class="chat__control__number">{{users.length}}</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import Channel from '~/types/Channel'
import { Prop } from '~/node_modules/vue-property-decorator'
import { UserChannelPermissions } from '~/helpers/permissions'
import { ChatConnect, ChatSendMessage } from '~/api/modules/chat'
import { ChatUpdateType, ChatUserInfo, ChatMessage } from '~/types/chat'
import { getReadableTime } from '~/helpers/time'

@Component({})
export default class Chat extends Vue {

  @Prop({required: true}) readonly channel!: Channel
  @Prop({required: true}) readonly permissions!: Array<UserChannelPermissions>
  showUsersList: boolean = false;
  ready: boolean = false;

  messages = [] as Array<ChatMessage>;
  users = [] as Array<ChatUserInfo>;
  socket: Websocket | null = null;
  form = {
    content: ''
  };

  beforeDestroy() {
    this.socket.close();
  }

  async mounted() {
    let connectKey;
    if (this.channel.domain) {
      const localStorageKeyDataId = 'connect_key_' + this.channel.id;
      if (localStorage.getItem(localStorageKeyDataId)) {
        let keyData = JSON.parse(localStorage.getItem(localStorageKeyDataId));
        if (keyData && (new Date()).getTime() - keyData.ts < 1000 * 60 * 3) {
          connectKey = keyData.key;
        } else {
          localStorage.removeItem(localStorageKeyDataId);
        }
      }
      connectKey = await ChatConnect(this.channel.id!);
      console.log(connectKey);
      localStorage.setItem(localStorageKeyDataId, JSON.stringify({
        key: connectKey,
        ts: (new Date()).getTime(),
      }))
    }
    const config = this.$accessor.modules.site?.config || {};
    this.socket = new WebSocket(
      `ws://${this.channel.domain || config.domain}/api/chat/${this.channel.id}/realtime${connectKey ? '?connect_key=' + connectKey : ''}`
    );
    this.socket.onmessage = (e: MessageEvent) => {
      this.handleUpdate(JSON.parse(e.data));
    }
  }

  handleUpdate({type, payload}: {type: ChatUpdateType, payload: any}) {
    switch (type) {
      case ChatUpdateType.CONNECTED:
        this.ready = true;
        break;
      case ChatUpdateType.MESSAGES_LIST:
        this.messages = payload.messages;
        break;
      case ChatUpdateType.USERS_LIST:
        this.users = payload.users;
        break;
      case ChatUpdateType.NEW_MESSAGE:
        this.messages.push(payload);
        break;
    }
  }

  sendMessage() {
    ChatSendMessage(this.socket, this.form);
    this.form.content = '';
  }

  getReadableTime = getReadableTime;
}
</script>
<style lang="scss">
.chat {
  height: 100%;
  background: var(--box-color);
  box-shadow: 0 0.5em 1em -0.75em rgba(0, 0, 0, .5);
  display: flex;
  flex-direction: column;
  position: relative;
  &__messages {
    flex: 1;
    overflow: auto;
    padding: .25em .75em;
    box-sizing: border-box;
  }
  &__message {
    margin: .5em 0;
    font-size: .9375em;
    &__user {
      color: var(--active-color);
      font-weight: 400;
      font-size: 1.0625em;
    }
    &__time {
      font-weight: bold;
      font-size: 1.0625em;
    }
  }
  &__input {
    background: var(--box-footer-color);
    padding: .5em;
    &__el {
      margin: 0 0 .5em;
    }
    &__bottom {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
  }

 &__control {
   position: relative;
   cursor: pointer;
   display: inline-flex;
   align-items: center;
   color: rgba(255, 255, 255, .75);
   user-select: none;
   transition: all .25s;
   &:hover {
     color: rgba(255, 255, 255, .875);
   }
   &--active {
     text-shadow: 0 0 .75em var(--active-color), 0 0 .75em var(--active-color);
     color: #fff;
   }
   &__number {
     margin: 0 0 0 .5em;
   }
 }
  &__users {
    position: absolute;
    top: .5em;
    left: .5em;
    background: var(--main-bg);
    width: calc(100% - 1em);
    box-shadow: 0 0 1em var(--active-color);
    padding: 1em;
    box-sizing: border-box;
    &__title {
      font-size: 1.25em;
      font-weight: 500;
      margin: 0 0 1em;
    }
  }
  &__user {
    display: flex;
    align-items: center;

    &__avatar {
      width: 2em;
      height: 2em;
      display: inline-block;
      background-size: cover;
    }

    &__name {
      font-size: 1.0625em;
      margin: 0 0 0 .5em;
    }


  }
}
</style>
