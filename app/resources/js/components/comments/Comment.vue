<template>
  <div class="comment" :class="{'comment--deleted': deleted}">
    <div class="comment__restore" v-if="deleted">
      <span class="comment__restore__title">{{ $t('comments.comment_deleted') }}</span>
      <c-button @click="restoreComment">{{ $t('comments.restore') }}</c-button>
    </div>
    <div class="comment__body">
      <div class="comment__info">
        <nuxt-link v-if="data.channel && data.channel.logo" :to="'/'+data.channel.shortname" class="comment__avatar"
                   :style="{backgroundImage: `url(${data.channel.logo})`}"></nuxt-link>
        <nuxt-link v-else-if="data.user && data.user.avatar" :to="'/users/'+data.user.id" class="comment__avatar"
                   :style="{backgroundImage: `url(${data.user.avatar})`}"></nuxt-link>

        <div class="comment__author">
          <nuxt-link v-if="data.channel" :to="'/'+data.channel.shortname" class="comment__username">
            {{ data.channel.name }}
          </nuxt-link>
          <nuxt-link v-else-if="data.user" :to="'/users/'+data.user.id" class="comment__username">
            {{ data.user.username }}
          </nuxt-link>
        </div>
        <div class="comment__time">
          <span class="comment__time__created">
            <c-tooltip position="bottom-left">{{ formatTimeAgo(data.created_at) }}</c-tooltip>
            {{ formatDate(data.created_at) }}
          </span>
          <span class="comment__time__edited" v-if="data.updated_at && data.updated_at !== data.created_at">
            <c-tooltip position="bottom-left">{{ formatTimeAgo(data.updated_at) }}</c-tooltip>
            {{ $t('comments.edited_at', {date: formatDate(data.updated_at)}) }}
          </span>
        </div>

        <c-button transparent narrow icon-only icon="fa-bars" @click="toolbarVisible = true">
          <c-popup-menu v-model="toolbarVisible">
            <c-popup-menu-item icon="edit" @click="editPanelVisible = true">{{ $t('comments.edit') }}
            </c-popup-menu-item>
            <c-popup-menu-item icon="close" @click="deleteComment">{{ $t('comments.delete') }}</c-popup-menu-item>
          </c-popup-menu>
        </c-button>

        <rating class="comment__rating" :data="data.rating" entity-type="comments" :entity-id="data.id"/>

      </div>
      <div v-if="!editPanelVisible" class="comment__main">
        <div class="comment__title" v-if="data.title">{{ data.title }}</div>
        <div class="comment__text" v-html="data.text"></div>
        <attachments-list class="comment__attachments" :attachments="data.attachments"
                          v-if="data.attachments && data.attachments.length > 0"/>
      </div>
      <comments-panel
          v-else
          :entity-type="entityType"
          :entity-id="entityId"
          :data="data"
          :enabled="panelEnabled"
          @edited="onCommentEdit"
          @hide="editPanelVisible = false"
      />
      <div class="comment__buttons" v-show="!editPanelVisible">
        <c-button flat class="comment__reply" v-if="panelEnabled && loggedIn" @click="replyPanelVisible = true">
          {{ $t('comments.reply') }}
        </c-button>
      </div>
    </div>
    <comments-panel
        v-if="replyPanelVisible"

        :enabled="panelEnabled"

        :parent-id="data.id"
        :channel="channel"

        @added="onNewComment"
        @hide="replyPanelVisible = false"

        :entity-type="entityType"
        :entity-id="entityId"
    />
    <div class="comment__children" v-if="children && children.length">
      <comment :channel="channel" :panelEnabled="panelEnabled" :data="child" :key="child.id" v-for="child in children"/>
      <a class="comment__children__show-all" v-if="data.children_count > children.length && !loadingChildren"
         @click="loadChildren()">{{ $t('comments.show_all_replies', {count: data.children_count}) }}</a>
      <c-preloader v-else-if="loadingChildren"/>
    </div>
  </div>
</template>
<script lang="ts" setup>
import Rating from '@/components/rating/Rating';
import AttachmentsList from '@/components/attachments/AttachmentsList';
import CommentsPanel from '@/components/comments/CommentsPanel';

const {formatDate, formatTimeAgo} = useDates();
const {loggedIn} = storeToRefs(useAuthStore());
const {request} = useApi();

const props = defineProps<{
  panelEnabled: boolean,
  data: Comments.Item,

  entityType: Entities.EntityType,
  entityId: Entities.EntityId,

  channel?: Channels.ItemBase,
}>();

const deleted = ref<boolean>(false);
const replyPanelVisible = ref<boolean>(false);
const editPanelVisible = ref<boolean>(false);
const toolbarVisible = ref<boolean>(false);

const loadingChildren = ref<boolean>(false);

const children = ref<Comments.Item[]>(props.data.children || []);

const onNewComment = (comment: Comments.Item) => {
  replyPanelVisible.value = false;
  children.value.push(comment);
}

const loadChildren = () => {
  loadingChildren.value = true;

  request.get(`/comments/:id/children`, {
    query: {
      after_id: children.value.length ? children.value[children.value.length - 1].id : null
    }
  }, {
    id: props.data.id
  }).then(loadedChildren => {
    children.value = [...children.value, ...loadedChildren];
    loadingChildren.value = false;
  })
}

const deleteComment = () => {
  request.delete(`/comments/:id`, {}, {
    id: props.data.id
  }).then(() => {
    deleted.value = true;
  });
}

const restoreComment = () => {
  request.post(`/comments/:id/restore`, {}, {
    id: props.data.id
  }).then(() => {
    deleted.value = false;
  })
}

</script>
<style lang="scss">
.comment {
  position: relative;
  font-size: 1rem;
  margin-bottom: .5em;
  padding-bottom: .5em;
  border-bottom: 1px solid var(--lighten-2);

  &:last-child {
    margin-bottom: 0;
    border-bottom: none;
  }

  &__body {
    position: relative;
  }

  &__title {
    font-weight: 600;
    font-size: 1.125em;
  }

  &__text {
    line-height: 1.4;

    p {
      margin: 0 0 .5em;

      &:last-of-type {
        margin: 0;
      }
    }
  }

  &__main {
    margin: .5em 0;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    @media screen and (max-width: 768px) {
      font-size: .875em;
    }
  }

  &__info {
    display: flex;
    align-items: center;
    gap: .5em;
    font-size: 1.125em;
  }

  &__toolbar {
    display: flex;
    font-size: .625em;

    @media screen and (max-width: 768px) {
      margin-left: auto;
      margin-right: 0;
    }
  }


  &__reply {
    cursor: pointer;
    font-size: .75em;
  }

  &__rating {
    font-size: .75em;
    margin-left: auto;
    @media screen and (max-width: 768px) {
      position: absolute;
      bottom: .25em;
      right: 0;
    }
  }

  &__avatar {
    width: 1.5em;
    height: 1.5em;
    display: block;
    background-size: contain;
    background-position: center center;
    background-repeat: no-repeat;
    @media screen and (max-width: 768px) {
      width: 2.5em;
      height: 2.5em;
    }
  }

  &__author {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
  }

  &__username {
    font-weight: 500;
    line-height: 0;
    font-size: .875em;
    text-decoration: none;
  }

  &__children {
    padding: 1em 0 0 1.25em;

    &__show-all {
      cursor: pointer;
      padding: 1em 0;
      display: inline-block;
      font-weight: 600;
      font-size: 1.0625em;
    }
  }

  &__time {
    font-size: .75em;
    display: flex;
    gap: .5em;

    @media screen and (max-width: 768px) {
      order: 2;
      width: 100%;
      font-size: .625em;
    }

    &__edited {
      font-weight: 500;
    }
  }

  &--deleted &__body {
    opacity: .25;
  }

  &__restore {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;

    &__title {
      margin-right: 1em;
    }
  }

  .theme-light & {
    margin: 0 0 .5em;
  }

  .theme-light &__body {
    box-shadow: 0 4px 70px -18px rgba(0, 0, 0, 0.25);
    padding: 1em;
  }
}

.bright .comment__info {
  background: linear-gradient(45deg, rgba(0, 0, 0, 0.1), transparent);
  border-bottom: 1px solid rgba(0, 0, 0, 0.15);
}
</style>

