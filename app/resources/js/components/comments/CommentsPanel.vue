<template>
  <div class="comments-panel" :class="{'comments-panel--edit': isEditing}">
    <div class="comments-panel__inputs">
      <div class="comments-panel__message" v-if="!loggedIn">
        <nuxt-link class="comments-panel__message__link" :to="`/auth/login?return=${route.fullPath}`">
          {{$t('comments.login_or_register')}}</nuxt-link>
        {{$t('comments.to_add')}}
      </div>
      <div class="comments-panel__message" v-else-if="!enabled && !isEditing" v-html="$t('comments.off')"></div>
      <div class="comments-panel__message" v-else-if="ban" v-html="banText"></div>

      <c-input v-if="form.from_channel_name && showInputs" v-model="form.title" :title="$t('comments.title')"/>
      <c-text-editor v-if="showInputs" v-model="form.text" :title="$t('comments.text')" />
    </div>
    <div class="comments-panel__bottom" v-if="showInputs">
      <div class="comments-panel__buttons" slot="buttons">
        <c-button :loading="loading" @click="sendComment()" :disabled="!form.text.length">
          {{isEditing ? $t('comments.save') : $t('comments.send')}}
        </c-button>
        <c-button @click="hidePanel()" flat v-if="parentId || isEditing">{{$t('global.cancel')}}</c-button>
        <c-checkbox :title="$t('comments.from_channel_name')" v-show="canWriteFromChannelName && !isEditing" v-model="form.from_channel_name"/>
      </div>
      <!--
      <attachments-panel @ready="onAttachmentsReady" @error="onAttachmentsError" v-model="attachments" :channel-id="form.from_channel_name ? entityId : null">

      </attachments-panel>
      --> <!-- TODO: attachments -->
    </div>
  </div>
</template>
<script lang="ts" setup>
import AttachmentsPanel from '@/components/attachments/AttachmentsPanel';

const { request } = useApi();
const { t } = useI18n();
const { formatDate } = useDates();
const { loggedIn } = storeToRefs(useAuthStore());
const route = useRoute();

const emit = defineEmits<{
  (e: 'edited', comment: Comments.Item): void,
  (e: 'added', comment: Comments.Item): void
  (e: 'hide'): void
}>();

const props = defineProps<{
  enabled: boolean,
  entityType: Entities.EntityType,
  entityId: Entities.EntityId,

  data?: Comments.Item,

  canWriteFromChannelName?: boolean,
  ban?: any, // todo
  parentId?: number,

  isEditing?: boolean,

}>();

const showInputs = computed(() => {
  return loggedIn && !props.ban && (props.isEditing || props.enabled);
})

const banText = computed(() => {
  return t('comments.ban.you_are_banned', {
    by_user: props.ban.banned_by_user ? ' ' + t('comments.ban.by_user', {user: `<strong>${props.ban.banned_by_user.username}</strong>`}) : '',
    till: props.ban.banned_till ? ' ' + t('comments.ban.till', {date: `<strong>${formatDate(props.ban.banned_till)}</strong>`}) : t('comments.ban.forever'),
    reason: props.ban.reason ? ' ' + t('comments.ban.reason', {reason: `<strong>${props.ban.reason}</strong>`}) : '',
  })
});

const form = ref<Comments.Body>({
  title: '',
  text: '',
  attachments: [],
  from_channel_name: false,
});

const loading = ref<boolean>(false);

const sendComment = (() => {
  loading.value = true;
  if (form.value.attachments.length === 0) {
    saveComment();
  } else {
    //emit('save_attachments');
  }
})

const saveComment = () => {

  const data: Comments.Body = {
    text: form.value.text,
    attachments: form.value.attachments,
  };
  if (form.value.from_channel_name) {
    data.title = form.value.title;
    data.from_channel_name = true;
  }

  if (props.data) {
    request.put('/comments/:id', {
      body: data
    }, {
      id: props.data.id
    }).then(comment => {
      emit('edited', comment);
    }).finally(() => {
      loading.value = false;
    });
  } else {
    data.entity_type = props.entityType;
    data.entity_id = props.entityId;

    if (props.parentId) {
      data.reply_to_comment_id = props.parentId;
    }

    request.post('/comments', {
      body: data
    }).then(comment => {
      emit('added', comment);
      form.value.title = '';
      form.value.text = '';
      form.value.attachments = [];
    }).finally(() => {
      loading.value = false;
    })
  }
}

const hidePanel = () => {
  emit('hide');
}
</script>

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
    &__message {
      background: var(--lighten-1);
      padding: 1em;
      border-radius: var(--border-radius);
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
