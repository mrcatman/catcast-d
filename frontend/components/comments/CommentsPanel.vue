<template>
  <div class="comments-panel" :class="{'comments-panel--edit': isEditing}" v-if="visible">
    <div class="comments-panel__inputs">
      <div class="comments-panel__login" v-if="!loggedIn">
        <nuxt-link class="comments-panel__login__link" :to="'/login?return='+$route.fullPath">{{$t('comments.login_or_register')}}</nuxt-link>{{$t('comments.to_add')}}
      </div>
      <div class="comments-panel__login" v-else-if="!canWrite && !isEditing" v-html="$t('comments.off')"></div>
      <div class="comments-panel__login" v-else-if="ban" v-html="banText"></div>
      <c-input v-if="fromChannelName && showInputs" v-model="title" :title="$t('comments.title')"/>
      <c-text-editor :simple="!fromChannelName" v-if="showInputs" v-model="text" :title="$t('comments.text')" />
    </div>
    <div class="comments-panel__bottom" v-if="showInputs">
      <attachments-panel @ready="onAttachmentsReady" @error="onAttachmentsError" v-model="attachments" :videos="fromChannelName" :channel-id="fromChannelName ? entityId : null">
        <div class="comments-panel__buttons" slot="buttons">
          <c-button :loading="loading" v-if="!isEditing" @click="sendComment()" :disabled="text.length === 0">{{$t('comments.send')}}</c-button>
          <c-button :loading="loading" v-else @click="sendEditedComment()" :disabled="text.length === 0">{{$t('comments.save')}}</c-button>
          <c-button @click="hidePanel()" flat v-if="parentId || isEditing">{{$t('global.cancel')}}</c-button>
          <c-checkbox :title="$t('comments.from_channel_name')" v-show="canWriteFromChannelName && !isEditing" v-model="fromChannelName"/>
        </div>
      </attachments-panel>
    </div>
  </div>
</template>
<style lang="scss">
  .comments-panel {
    width: 100%;
    padding: 1em;
    border-bottom: 1px solid var(--lighten-1);
    box-sizing: border-box;
    --vertical-margin: 0 0 1em;
    &--edit {
      padding: 0;
      border-bottom: 0;
    }
    &__login {
      background: var(--lighten-1);
      padding: 1em;
      border-radius: .25em;
      box-shadow: 0 0.5em 1.5em -0.25em var(--lighten-1);
      &__link {
        text-decoration: none;
        border-bottom: 1px solid;
      }
    }
    &__text {
      width: calc(100% - 2.5em);
      resize: none;
      min-height: 5em;
      border: 0;
      margin: .5em;
      font: inherit;
      padding: 1em;
      font-size: 1em;
      outline: none;
      background: rgba(0, 0, 0, 0.1);
      .theme-default & {
        border-bottom: 2px solid rgba(255,255,255,0.15);
      }
    }
    &__bottom{
      width: 100%;
      display:flex;
      justify-content:space-between;
      align-items:center;
    }
    &__attachment-buttons{
      .button{
        margin: 0 0 0 .5em;
      }
    }
    &__buttons{
      margin: 0;
      display: flex;
      align-items: center;
      .button {
        margin: 0 1em 0 0;
      }
    }
  }
  @media screen and (max-width: 768px) {
    .comments-panel__bottom {
      flex-direction: column;
      align-items: flex-start;
    }
  }
</style>
<script>
import { formatFullDate } from "@/helpers/dates";
  import AttachmentsPanel from '@/components/attachments/AttachmentsPanel';
import {mapState} from "vuex";

  export default {
    components: {
      AttachmentsPanel
    },
    computed: {
      ...mapState('auth', ['loggedIn']),
      showInputs() {
        return this.loggedIn && !this.ban && (this.isEditing || this.canWrite);
      },
      banText() {
        const ban = this.ban;
        return this.$t('comments.ban.you_are_banned', {
          by_user: ban.banned_by_user ? ' ' + this.$t('comments.ban.by_user', {user: `<strong>${ban.banned_by_user.username}</strong>`}) : '',
          till: ban.banned_till ? ' ' + this.$t('comments.ban.till', {date: `<strong>${formatFullDate(ban.banned_till)}</strong>`}) : this.$t('comments.ban.forever'),
          reason: ban.reason ? ' ' + this.$t('comments.ban.reason', {reason: `<strong>${ban.reason}</strong>`}) : '',
        })
      },
    },
    props: {
      hasParent: Boolean,
      canWrite: Boolean,
      canWriteFromChannelName: Boolean,
      ban: {
        type: [Object, Boolean],
        required: false
      },
      parentId: Number,
      entityType: String,
      entityId: Number,
      entityUuid: String,
      isEditing: Boolean,
      data: Object
    },
    data() {
      return {
        visible: true,
        title: '',
        text: '',
        attachments: [],
        fromChannelName: !!this.data?.channel_id,
        loading: false,
      }
    },
    mounted() {
      if (this.isEditing) {
        this.title = this.data.title;
        this.text = this.data.text;
        this.attachments = this.data.attachments;
      }
    },

    methods: {
      onAttachmentsReady() {
        if (this.isEditing) {
          this.saveEditedComment();
        } else {
          this.saveComment();
        }
      },
      onAttachmentsError() {
        this.loading = false;
      },
      sendEditedComment() {
        if (this.text.length > 0) {
          this.loading = true;
          if (this.attachments.length === 0) {
            this.saveEditedComment();
          } else {
            this.$emit('save_attachments');
          }
        }
      },
      saveEditedComment() {
        const data = {
          text: this.text,
          attachments: this.attachments,
        };
        if (this.fromChannelName) {
          data.title = this.title;
          data.from_channel_name = true;
        }
        this.loading = true;
        this.$api.put(`/comments/${this.data.id}`, data).then(editedComment => {
          this.$emit('edited', editedComment);
        }).finally(() => {
          this.loading = false;
        })
      },
      sendComment() {
        if (this.text.length > 0) {
          this.loading = true;
          if (this.attachments.length === 0) {
            this.saveComment();
          } else {
            this.$emit('save_attachments');
          }
        }
      },
      saveComment() {
        const data = {
          text: this.text,
          attachments: this.attachments,
          entity_type: this.entityType,
          entity_id: this.entityId,
        };
        if (this.fromChannelName) {
          data.title = this.title;
          data.from_channel_name = true;
        }
        if (this.parentId) {
          data.reply_to_comment_id = this.parentId;
        }
        if (this.entityUuid) {
          data.uuid = this.entityUuid;
        }
        this.$api.post('comments', data).then(data => {
          this.$emit('added', data);
          this.title = '';
          this.text = '';
          this.attachments = [];
        }).finally(() => {
          this.loading = false;
        })
      },
      hidePanel() {
        this.$emit('hide');
      }
    }
  }
</script>
