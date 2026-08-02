<template>
  <custom-colors class="chat" :colors-scheme="channel?.colors_scheme">
    <standard-modal />

    <div class="chat__top">
      <div class="chat__top__left">
        <span class="chat__top__heading">{{$t('chat.heading')}}</span>
      </div>
      <div class="buttons-row" v-if="!state.loading">
        <c-button flat @click="showUsersList()" icon="people">{{users.length}}</c-button>
        <c-button flat icon-only v-if="config.motd && config.motd.length > 0 && !showMOTD" @click="showMOTD = true" icon="fa-exclamation-triangle"></c-button>
        <chat-actions v-if="me"
           :config="config"
           :channel="channel"
           :me="me"
           :is-team-member="isTeamMember"
           :hide-service-messages="hideServiceMessages"
           :guest-username="guestUsername"
           @toggleServiceMessages="toggleServiceMessages"
           @changeGuestUsername="changeGuestUsername"
        />
      </div>
    </div>

    <div class="chat__main">
      <c-preloader block v-if="state.loading || state.connecting" />
      <div class="chat__messages"  ref="messages">
        <chat-motd v-if="config.motd && config.motd.length > 0" v-model="showMOTD" :motd="config.motd" />
        <chat-message v-for="(message, $index) in messages"
            :key="$index"
            v-if="!message.is_service || !hideServiceMessages"
            :message="message"
            :is-team-member="isTeamMember"
            @reply="reply"
            @replyToUser="replyToUser"
        />
      </div>
    </div>

    <chat-error :state="state" @reconnect="reconnect" />
    <div class="chat__panel">
      <div v-if="config.disabled" class="chat__panel__disabled">
        {{$t('chat.disabled')}}
      </div>
      <div v-else-if="me && me.is_banned" class="chat__panel__disabled">
        {{$t('chat.you_are_banned')}}
      </div>
      <div v-else class="chat__panel__bottom">
        <chat-input ref="input" v-model="message" :loaded="loaded" @sendMessage="sendMessage" />
        <div class="chat__panel__actions">
          <chat-smileys :config="config" @addSmiley="addSmiley" />
          <c-colorpicker small v-model="color" />
          <c-button :loading="message.sending" :disabled="!loaded || !message.data.text.trim().length" @click="sendMessage()" icon="send" class="chat__send">{{$t('chat.send')}}</c-button>
        </div>
      </div>
    </div>
  </custom-colors>
</template>
<style lang="scss">
  .chat {
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--channel-colors-inside-background);
    color: var(--channel-colors-inside-texts);
    --menu-color: var(--channel-colors-inside-panels);
    --box-color: var(--channel-colors-inside-panels);
    .button:not(.button--flat):not(.button--transparent) {
      background: var(--channel-colors-inside-buttons) !important;
      color: var(--channel-colors-inside-buttons-texts) !important;
    }

    &__main {
      flex: 1;
      position: relative;
      display: flex;
    }

    &__messages {
      width: 100%;
      height: 100%;
      overflow: auto;
      overflow-x: hidden;
    }


    &__top {
      background: var(--channel-colors-inside-panels);
      padding: .5em;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
      z-index: 10000;
      &__heading {
        font-size: 1.125em;
        font-weight: bold;
      }
    }

    &__panel {
      padding: .625em .5em;
      background: var(--channel-colors-inside-panels);
      box-shadow: 0 0 1.75em -0.75em var(--channel-colors-inside-panels);
      z-index: 10000;

      &__bottom {
        position: relative;
      }

      &__actions {
        margin-top: .5em;
        display: flex;
        align-items: center;
        justify-content: space-between;
      }

      &__disabled {
        font-size: 1.125em;
        font-weight: bold;
        padding: 1em;
      }
    }




    &__smiley {
      width: 2em;
      margin: .25em;
      cursor: pointer;
      &:hover {
        opacity: .85;
      }
    }


    &__send {
      margin-left: auto;
    }

    &--transparent {
      background: none;
    }

    &--transparent &__top {
      background: rgba(255, 255, 255, 0.15);
    }

    &--transparent &__panel {
      background: rgba(255, 255, 255, 0.175);
    }

    &--transparent &__messages {
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
    }
  }
</style>
<script>

  import CustomColors from "@/components/CustomColors";
  import ChatActions from "@/components/chat/ChatActions";

  import ChatUsers from "@/components/chat/ChatUsers";
  import ChatMessage from "@/components/chat/ChatMessage";
  import ChatMotd from "@/components/chat/ChatMOTD";
  import ChatError from "@/components/chat/ChatError.vue";
  import ChatSmileys from "@/components/chat/ChatSmileys.vue";
  import StandardModal from "@/components/StandardModal.vue";
  import ChatInput from "@/components/chat/ChatInput.vue";

  const merge = (obj, obj2) => {
    Object.keys(obj2).forEach(key => {
      obj[key] = obj2[key];
    })
  }

  let lastColorChangeTimeout = null;

  export default {
    components: {ChatInput, StandardModal, ChatSmileys, ChatError, ChatMotd, ChatMessage, ChatActions, CustomColors},
    computed: {
      loaded() {
        return !this.state.loading && !this.state.connecting && !this.state.loading_error && !this.state.connect_error;
      },
      isTeamMember() {
        const me = this.me;
        return !!(me && me.permissions && (me.permissions.owner || me.permissions.channel_admin || me.permissions.admin || me.permissions.moderation));
      },
    },
    watch: {
      hideServiceMessages(hide) {
        localStorage.hide_service_messages = hide;
      },
      color(newColor) {
        localStorage.chat_color = newColor;
        clearTimeout(lastColorChangeTimeout);
        lastColorChangeTimeout = setTimeout(this.changeColor, 1000);
      }
    },
    async mounted() {
      await this.load();
      this.scrollToBottom();
      this.connect();
    },
    data() {
      return {
        state: {
          loading: true,
          connecting: true,
          loading_error: null,
          connect_error: null
        },
        connection: null,
        me: null,
        users: [],
        messages: [],
        message: {
          sending: false,
          data: {
            reply_to: [],
            text: '',
          },
          errors: {},
        },
        color: localStorage.chat_color || '#fff',
        guestUsername: '',
        guestId: null,
        guestAccessKey: null,
        showMOTD: true,
        config: {},
        hideServiceMessages: localStorage.hide_service_messages === 'true'
      }
    },
    beforeDestroy() {
      this.$echo.leave(`App.Chat.${this.channel.id}`)
    },
    methods: {
      async reconnect() {
        if (this.state.loading_error) {
          this.state.loading_error = false;
          this.load();
        }
        if (this.state.connect_error) {
          this.state.connect_error = false;
          this.connect();
        }
      },
      async load() {
        try {
          this.messages = await this.$api.get(`chat/${this.channel.id}/messages`);
          this.config = await this.$api.get(`chat/${this.channel.id}/config`);

          this.guestUsername = localStorage.chat_guest_username || this.config.default_guest_username || 'Guest';
          // this.users = await this.$api.get(`chat/${this.channel.id}/users`);
        } catch (e) {
          this.state.loading_error = true;
        } finally {
          this.state.loading = false;
        }
      },
      async connect() {
        this.state.connecting = true;
        if (this.connection) {
            this.connection.socket.disconnect();
        }

        if (!this.$store.state.auth.loggedIn) {
          const authorizationData = await this.$api.post(`chat/${this.channel.id}/authorize-guest`); // todo: store locally
          this.guestId = authorizationData.guest_id;
          this.guestAccessKey = authorizationData.guest_access_key;
        }

        this.$echo.connector.options.auth.headers['X-Chat-Color'] = localStorage.chat_color || '#fff';
        this.$echo.connector.options.auth.headers['X-Guest-Id'] = this.guestId;

        this.connection = this.$echo.join(`App.Chat.${this.channel.id}`)
          .here((users) => {
            this.state.connecting = false;
            this.setUsers(users);
          })
          .joining((user) => {
            this.onUserEntered(user);
          })
          .leaving((user) => {
            this.onUserLeft(user);
          })
          .listen('.subscription_error', () => {
             this.state.connecting = false;
             this.state.connect_error = true;
          })
          .listen('.chat.message', (e) => {
            this.onNewMessage(e.message);
          }).listen('.chat.guest_state_changed', (e) => {
            const user = this.users.filter(user => user.is_guest && user.id === e.guest?.id)[0];
            if (user) {
              if (e.state.username) {
                this.messages.push({
                  is_service: true,
                  user_id: null,
                  created_at: new Date(),
                  color: user.color,
                  username: user.username,
                  text: this.$t('chat._service_messages.guest_changed_username', {username: `<strong style="color: ${user.color}">${e.state.username}</strong>`}),
                });
              }
              if (e.state.is_banned !== undefined) {
                this.messages.push({
                  is_service: true,
                  user_id: null,
                  created_at: new Date(),
                  color: e.user?.color,
                  username: e.user?.username,
                  text: this.$t(e.state.is_banned ? 'chat._service_messages.guest_banned' : 'chat._service_messages.guest_unbanned', {username: `<strong style="color: ${user.color}">${user.username}</strong>`}),
                });
              }
              merge(user, e.state);
            }
            if (this.me.is_guest && this.me.id === e.guest?.id) {
              merge(this.me, e.state);
            }
          }).listen('.chat.user_state_changed', (e) => {
            const user = this.users.filter(user => !user.is_guest && user.id === e.user_to.id)[0];
            if (user) {
              if (e.state.is_banned !== undefined) {
                this.messages.push({
                  is_service: true,
                  user_id: null,
                  created_at: new Date(),
                  color: e.user_from?.color,
                  second_color: e.user_to?.color,
                  username: e.user_from?.username,
                  second_username: e.user_to?.username,
                  text: this.$t(e.state.is_banned ? 'chat._service_messages.user_banned' : 'chat._service_messages.user_unbanned', {username: `<strong style="color: ${user.color}">${user.username}</strong>`}),
                });
              }
              merge(user, e.state);
            }
            if (!this.me.is_guest && this.me.id === e.user_to.id) {
              merge(this.me, e.state);
            }
          }).listen('.chat.state_changed', (e) => {
            merge(this.config, e.state);
          }).listen('.chat.clear', (e) => {
            this.messages = [];
            this.messages.push({
              is_service: true,
              user_id: e.user?.id,
              created_at: new Date(),
              color: e.user?.color,
              username: e.user?.username,
              text: this.$t('chat._service_messages.cleared'),
            });
          }).listen('.chat.message_deleted', (e) => {
            const message = this.messages.filter(item => item.id === e.message.id)[0];
            if (message) {
              this.$set(message, 'is_deleted', true);
            }
          })
      },
      showUsersList() {
        this.$store.commit('modals/showStandardModal', {
          confirm: false,
          title: this.$t('chat.users'),
          component: ChatUsers,
          props: {
            parentComponent: this,
            channel: this.channel,
            config: this.config,
            users: this.users,
            isTeamMember: this.isTeamMember
          },
        })
      },
      toggleServiceMessages() {
        this.hideServiceMessages = !this.hideServiceMessages;
      },

      replyToUser(user) {
        this.message.data.reply_to.push({
          color: user.color,
          user_id: user.id,
          username: user.username
        });

        this.$nextTick(() => {
          this.$refs.input.focus();
        })
      },
      reply(message) {
        let data = {
          color: message.color,
          username: message.username,
          id: message.id
        };
        if (message.is_from_bot) {
          data.bot_id = message.bot_id;
        }
        if (message.is_registered) {
          data.user_id = message.user_id;
        }

        this.message.data.reply_to.push(data);
        this.$nextTick(() => {
          this.$refs.input.focus();
        })
      },
      addSmiley(smiley) {
        this.message.data.text+= `:${smiley.code}:`;
        this.$nextTick(() => {
          this.$refs.input.focus();
        })
      },
      changeUserBanState(user, state) {
        this.$api.post(`/chat/${this.channel.id}/ban`, {user_id: user.id, state});
      },
      changeGuestBanState(user, state) {
        this.$api.post(`/chat/${this.channel.id}/ban-guest`, {guest_id: user.guest_id, state});
      },
      changeColor() {
        this.$api.post(`/chat/${this.channel.id}/color`, {
          color: this.color,
          guest_id: this.guestId,
          access_key: this.guestAccessKey,
        })
      },
      changeGuestUsername({username}) {
        this.guestUsername = username;
        localStorage.chat_guest_username = username;
      },
      onNewMessage(message) {
        this.messages.push(message);
        this.scrollToBottom();
      },
      sendMessage() {
        if (this.message.data.text.length > 0) {
          this.message.sending = true;
          this.message.data.text = this.message.data.text.match(/[^\r\n]+/g)[0];
          const text = this.message.data.text.replace(/<img data-smiley-id="(.*?)" class="smiley" src="(.*?)>/gm, ':$1:');
          this.$api.post(`chat/${this.channel.id}/messages`, {
            guest_id: this.guestId,
            access_key: this.guestAccessKey,
            reply_to: this.message.data.reply_to,
            text,
            color: this.color
          }).then(() => {
            this.message.data.reply_to = [];
            this.message.data.text = '';
          }).finally(() => {
            this.message.sending = false;
          })
        }
      },


      onUserEntered(user) {
        if (!this.config.disabled) {
          this.messages.push({
            is_service: true,
            user_id: !user.is_guest ? user.id : null,
            created_at: new Date(),
            color: user.color,
            username: user.username,
            text: this.$t('chat._service_messages.user_entered'),
          });
          this.scrollToBottom();
        }
        this.users.push(user);
      },
      onUserLeft(user) {
        if (!this.config.disabled) {
          this.messages.push({
            is_service: true,
            user_id: !user.is_guest ? user.id : null,
            created_at: new Date(),
            color: user.color,
            username: user.username,
            text: this.$t('chat._service_messages.user_left'),
          });
          this.scrollToBottom();
        }
        const index = this.users.map(userItem => userItem.id).indexOf(user.id);
        if (index >= 0) {
          this.users.splice(index, 1);
        }
      },
      setUsers(users) {
        this.users = users;
        if (!this.me) {
          this.me = users[0];
        }
      },
      scrollToBottom() {
        this.$nextTick(() => {
          if (this.$refs.messages) {
            this.$refs.messages.scrollTop = 10000000;
          }
        })
      }
    },
    props: {
      channel: {
        type: Object,
        required: true,
      }
    },
  }
</script>
