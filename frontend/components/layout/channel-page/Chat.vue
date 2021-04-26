<template>
  <div class="chat">

    <div class="chat__block chat__users" v-show="showUsersList">
      <div class="chat__block__title">
        {{$t('chat.users')}}
        <m-button @click="showUsersList = false" icononly rounded flat icon="close" />
      </div>

      <a :target="user.webUrl ? '_blank' : null" :href="user.webUrl"  class="chat__user" :class="{'chat__user--blocked': user.isBlocked}" v-for="user in users">
        <span class="chat__user__avatar" v-if="user.avatar" :style="{backgroundImage: `url(${user.avatar})`}"></span>
        <span class="chat__user__name" :style="{color: user.color}">{{getUserHandle(user)}}</span>
        <div class="chat__user__buttons" v-if="canEditUser(user)">
          <m-button @click="ban(user)">{{user.isBlocked ? $t('chat.unban_user') : $t('chat.ban_user')}}</m-button>
        </div>
      </a>
    </div>
    <div class="chat__block chat__settings" v-if="showSettings">
      <div class="chat__block__title">
        {{$t('chat.user_settings')}}
        <m-button @click="showSettings = false" icononly rounded flat icon="close" />
      </div>
      <div class="chat__settings__input chat__settings__input--inline">
        <label class="chat__settings__input__label">{{$t('chat.color')}}</label>
        <m-colorpicker v-model="color" />
      </div>
      <div v-if="me && (me.isAdmin || me.isModerator)">
        <div class="chat__block__delimiter"></div>
        <div class="chat__block__title">
          {{$t('chat.settings')}}
        </div>
        <div class="chat__settings__input">
          <m-checkbox v-model="formSettings.disabled" :title="$t('chat.disable')"></m-checkbox>
        </div>
        <div class="chat__settings__input">
          <m-input :title="$t('chat.motd')" v-model="formSettings.motd" />
        </div>
        <m-button @click="saveSettings()">{{$t('forms.save')}}</m-button>
        <div class="chat__block__delimiter"></div>
        <m-button big @click="clearChat()">{{$t('chat.clear')}}</m-button>
      </div>

    </div>

    <div class="chat__motd" v-if="settings && settings.motd">
      {{settings.motd}}
    </div>

    <div class="chat__messages">
      <div class="chat__message" v-for="message in messages" :key="message.id">
        <span class="chat__message__time">{{ getReadableTime(message.timestamp) }}</span>
        <a v-if="message.author" :target="message.author.webUrl ? '_blank' : null" :href="message.author.webUrl" class="chat__message__user" :style="{color: message.color}">{{ getUserHandle(message.author) }}</a>
        {{message.content}}
        <span class="chat__message__delete" v-if="canEditMessage(message)" @click="deleteMessage(message)">
          <i class="material-icons">clear</i>
        </span>
      </div>
    </div>
    <div class="chat__input">
      <m-input v-if="me && !settings.disabled && !me.isBlocked" type="textarea" v-model="form.content" title="" class="chat__input__el" />
      <div class="chat__input__disabled" v-else-if="settings.disabled">{{$t('chat.disabled')}}</div>
      <div class="chat__input__blocked" v-else-if="me.isBlocked">{{$t('chat.blocked')}}</div>
      <div class="chat__input__bottom">
        <m-button v-if="me && !settings.disabled && !me.isBlocked" @click="sendMessage()" :disabled="form.content.length === 0">{{$t('common.send')}}</m-button>
        <div class="chat__controls">
          <a @click="showUsersList = !showUsersList" class="chat__control" :class="{'chat__control--active': showUsersList}">
            <span class="tooltip">{{$t('chat.users')}}</span>
            <i class="material-icons">supervisor_account</i>
            <span class="chat__control__number">{{membersCount}}</span>
          </a>
          <a @click="showSettings = !showSettings" class="chat__control" :class="{'chat__control--active': showSettings}">
            <span class="tooltip">{{$t('chat.settings')}}</span>
            <i class="material-icons">settings</i>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import Channel from '~/types/Channel'
import { Prop, Watch } from '~/node_modules/vue-property-decorator'
import { UserChannelPermissions } from '~/helpers/permissions'
import {
  ChatBanUser,
  ChatUnbanUser,
  ChatClear,
  ChatConnect,
  ChatDeleteMessage,
  ChatSendMessage,
  ChatSetColor,
  ChatUpdateSettings,
} from '~/api/modules/chat'
import { ChatMessage, ChatSettings, ChatUpdateType, ChatUserInfo } from '~/types/chat'
import { getReadableTime } from '~/helpers/time'

@Component({})
export default class Chat extends Vue {

  @Prop({required: true}) readonly channel!: Channel
  @Prop({required: true}) readonly permissions!: Array<UserChannelPermissions>
  showUsersList: boolean = false;
  showSettings: boolean = false;
  ready: boolean = false;

  messages = [] as Array<ChatMessage>;
  me: ChatUserInfo | null = null;
  users = [] as Array<ChatUserInfo>;
  socket: Websocket | null = null;
  form = {
    content: ''
  };
  settings = {
    disabled: true,
    motd: ''
  } as ChatSettings;
  formSettings = JSON.parse(JSON.stringify(this.settings)) as ChatSettings;

  membersCount = 0;
  color = localStorage.catcast_chat_color || '#0f0';

  get user() {
    return this.$accessor.modules.auth.me;
  }

  beforeDestroy() {
    this.socket && this.socket.close();
  }

  async mounted() {
    let connectKey;
    if (this.user && this.channel.domain) {
      const localStorageKeyDataId = 'connect_key_' + this.channel.url;
      if (localStorage.getItem(localStorageKeyDataId)) {
        let keyData = JSON.parse(localStorage.getItem(localStorageKeyDataId)!);
        if (keyData && (new Date()).getTime() - keyData.ts < 1000 * 60 * 3) {
          connectKey = keyData.key;
        } else {
          localStorage.removeItem(localStorageKeyDataId);
        }
      }
      connectKey = await ChatConnect(this.channel.id!);
      localStorage.setItem(localStorageKeyDataId, JSON.stringify({
        key: connectKey,
        ts: (new Date()).getTime(),
      }))
    }
    const config = this.$accessor.modules.site?.config || {};
    this.socket = new WebSocket(
      `ws://${this.channel.domain || config.domain}/api/chat/${this.channel.url}/realtime${connectKey ? '?connect_key=' + connectKey : ''}`
    );
    this.socket.onmessage = (e: MessageEvent) => {
      this.handleUpdate(JSON.parse(e.data));
    }
  }

  @Watch('color')
  onChangeColor(color: string){
    ChatSetColor(this.socket, color);
    localStorage.catcast_chat_color = color;
  }

  handleUpdate({type, payload}: {type: ChatUpdateType, payload: any}) {
    switch (type) {
      case ChatUpdateType.CONNECTED:
        this.ready = true;
        this.me = payload.user;
        ChatSetColor(this.socket, this.color);
        break;
      case ChatUpdateType.MESSAGES_LIST:
        this.messages = payload.messages;
        break;
      case ChatUpdateType.USERS_LIST:
        this.users = payload.users;
        break;
      case ChatUpdateType.USER_JOINED:
        console.log(payload);
        this.users.push(payload.user);
        break;
      case ChatUpdateType.USER_LEFT:
        this.users = this.users.filter(user => user.login !== payload.user.login || user.domain !== payload.user.domain);
        break;
      case ChatUpdateType.NEW_MESSAGE:
        this.messages.push(payload.message);
        break;
      case ChatUpdateType.MEMBERS_COUNT:
        this.membersCount = parseInt(payload.count);
        break;
      case ChatUpdateType.MESSAGE_DELETED:
        this.messages = this.messages.filter(message => message.id !== payload.id);
        break;
      case ChatUpdateType.CHAT_SETTINGS:
        this.settings = payload.settings;
        this.formSettings = JSON.parse(JSON.stringify(payload.settings));
        break;
      case ChatUpdateType.SET_USER_COLOR:
        this.users.forEach(user => {
          if (user.id === payload.id) {
            user.color = payload.color;
          }
        })
        break;
      case ChatUpdateType.CHAT_CLEARED:
        this.messages = [];
        break;
      case ChatUpdateType.USER_INFO_CHANGED:
        this.users.forEach((user, index) => {
          if (payload.id === user.id) {
            this.$set(this.users, index, {...user, ...payload.info});
          }
        })
        if (payload.id === this.me.id) {
          this.me = {...this.me, ...payload.info};
        }
        break;
    }
  }

  deleteMessage(message: ChatMessage) {
    ChatDeleteMessage(this.socket, {
      id: message.id
    });
  }

  sendMessage() {
    ChatSendMessage(this.socket, this.form);
    this.form.content = '';
  }

  saveSettings() {
    ChatUpdateSettings(this.socket, this.formSettings);
    this.showSettings = false;
  }

  clearChat() {
    ChatClear(this.socket);
    this.showSettings = false;
  }

  ban(user) {
    return !user.isBlocked ? ChatBanUser(this.socket, user.id) : ChatUnbanUser(this.socket, user.id);
  }

  canEditMessage(message: ChatMessage): boolean {
    const me = this.me;
    const author = message.author;
    if (!me || !author) {
      return false;
    }
    if (author.domain === me.domain && author.login === me.login) {
      return true;
    }
    if (me.isAdmin) {
      return true;
    }
    if (me.isModerator && !author.isModerator && !author.isAdmin) {
      return true;
    }
    return false;
  }

  canEditUser(user: ChatUserInfo) {
    const me = this.me;
    if (!me) {
      return false;
    }
    if (me.isModerator && (!user.isModerator && !user.isAdmin)) {
      return true;
    }
    if (me.isAdmin && !user.isAdmin) {
      return true;
    }
    return false;
  }


  getReadableTime = getReadableTime;

  getUserHandle (user: ChatUserInfo): string {
    if (!user) {
      return '';
    }
    if (user.domain) {
      return `${user.login}@${user.domain}`;
    }
    return user.login;
  }
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
  &__block {
    position: absolute;
    left: .25em;
    top: .25em;
    z-index: 10000;
    padding: .5em 1em;
    background: var(--main-bg);
    width: calc(100% - .5em);
    box-sizing: border-box;
    &__delimiter {
      width: 100%;
      height: 1px;
      background: var(--border-color);
      margin: .75em 0;
    }
    &__title {
      font-size: 1.25em;
      font-weight: bold;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: .25em 0;

      .button {
        font-size: .5em;
      }
    }
  }
  &__motd {
    border-left: .25em solid var(--active-color);
    margin: .5em .5em 0;
    padding: .5em;
    background: var(--box-footer-color);
    font-weight: 400;
  }
  &__settings {
    &__input {
      padding: .5em 0;
      margin: -.5em 0;
      &--inline {
        display: flex;
        align-items: center;
      }
      &__label {
        margin: 0 .5em 0 0;
      }
    }


  }
  &__messages {
    flex: 1;
    overflow: auto;
    padding: .25em .75em;
    box-sizing: border-box;
    position: relative;
  }

  &__message {
    position: relative;
    margin: .5em 0;
    font-size: .9375em;

    &__user {
      color: var(--active-color);
      font-weight: 400;
      font-size: .875em;
    }

    &__time {
      font-weight: bold;
      font-size: .875em;
    }

    &__delete {
      position: absolute;
      top: .25em;
      right: 0;
      font-size: .75em;
      cursor: pointer;
      opacity: .5;
      transition: all .25s;

      &:hover {
        opacity: .75;
      }
    }
  }
  &__input {
    background: var(--box-footer-color);
    padding: .5em;
    position: relative;
    &__disabled {
      font-size: 1.125em;
      position: absolute;
      top: .625em;
      left: .5em;
    }
    &__el {
      margin: 0 0 .5em;
    }

    &__bottom {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    &__blocked {
      display: flex;
      align-items: center;
      background: var(--negative-color);
      padding: .5em;
      margin: 0 0 .5em;

      &:before {
        content: "!";
        font-weight: bold;
        font-size: 1.25em;
        margin: 0 .5em 0 0;
      }
    }
  }
  &__controls {
    margin-left: auto;
  }
 &__control {
   position: relative;
   cursor: pointer;
   display: inline-flex;
   align-items: center;
   color: rgba(255, 255, 255, .875);
   user-select: none;
   transition: all .25s;
   margin: 0 0 0 1em;
   &:hover {
     color: rgba(255, 255, 255, 1);
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
    text-decoration: none;

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
    &__buttons {
      margin-left: auto;
      font-size: .75em;
    }
    &--blocked &__avatar, &--blocked &__name {
      text-decoration: line-through;
      opacity: .5;
    }
  }
}
</style>
