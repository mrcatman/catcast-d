<template>
<div class="comment" :class="{'comment--deleted': deleted}">
	<div class="comment__restore" v-if="deleted">
    <span class="comment__restore__title">{{$t('comments.comment_deleted')}}</span>
    <c-button @click="restoreComment">{{$t('comments.restore')}}</c-button>
	</div>
	<div class="comment__body">
		<div class="comment__info">
      <nuxt-link v-if="comment.channel" v-show="comment.channel.logo" :to="'/'+comment.channel.shortname" class="comment__avatar" :style="{backgroundImage: `url(${comment.channel.logo})`}"></nuxt-link>
      <nuxt-link v-else-if="comment.user" v-show="comment.user.avatar" :to="'/users/'+comment.user.id" class="comment__avatar" :style="{backgroundImage: `url(${comment.user.avatar})`}"></nuxt-link>

			<div class="comment__author" >
        <nuxt-link v-if="comment.channel"  :to="'/'+comment.channel.shortname" class="comment__username">{{comment.channel.name}}</nuxt-link>
        <nuxt-link v-else-if="comment.user" :to="'/users/'+comment.user.id" class="comment__username">{{comment.user.username}}</nuxt-link>

        <div class="comment__time">
          <span class="comment__time__created">
            <c-tooltip>{{formatFullDate(comment.created_at)}}</c-tooltip>
            {{formatPublishDate(comment.created_at)}}
          </span>
          <span class="comment__time__edited" v-if="comment.updated_at && comment.updated_at !== comment.created_at">
            <c-tooltip>{{formatFullDate(comment.updated_at)}}</c-tooltip>
            {{$t('comments.edited_at', {date: formatPublishDate(comment.updated_at)})}}
          </span>
        </div>
        <div class="comment__toolbar" v-if="comment.can_edit">
          <c-button transparent narrow icon-only icon="edit" @click="editPanelVisible = true">
            <template slot="tooltip"><c-tooltip position="bottom-left">{{$t('comments.edit')}}</c-tooltip></template>
          </c-button>
          <c-button transparent narrow icon-only icon="close" @click="deleteComment()">
            <template slot="tooltip"><c-tooltip position="bottom-left">{{$t('comments.delete')}}</c-tooltip></template>
          </c-button>
        </div>
        <rating class="comment__rating" :data="comment.rating" entity-type="comments" :entity-id="comment.id" />
      </div>
		</div>
    <div v-if="!editPanelVisible" class="comment__main">
      <div class="comment__title" v-if="comment.title">{{comment.title}}</div>
      <div class="comment__text"  v-html="comment.display_text"></div>
      <attachments-list class="comment__attachments" :attachments="comment.attachments" v-if="comment.attachments && comment.attachments.length > 0"/>
    </div>
		<comments-panel v-else :isEditing="true" :entity-id="entityId" @edited="onCommentEdit"  :data="comment"  @hide="editPanelVisible = false"  />
    <div class="comment__buttons" v-show="!editPanelVisible">
      <c-button flat class="comment__reply" v-if="canWrite && loggedIn" @click="replyPanelVisible = true">{{$t('comments.reply')}}</c-button>
		</div>
	</div>
	<div class="comment__children" v-if="comment.children && comment.children.length">
		<comment :channel="channel" :canWrite="canWrite" :data="child" :key="child.id" v-for="child in comment.children" />
    <a class="comment__children__show-all" v-if="comment.children_count > comment.children.length && !loadingChildren" @click="loadChildren()">{{$t('comments.show_all_replies', {count: comment.children_count})}}</a>
	  <c-preloader v-else-if="loadingChildren"/>
  </div>
  <div class="comment__reply-panel" v-if="replyPanelVisible && canWrite">
    <comments-panel
      :can-write="canWrite"
      :has-parent="true"
      :channel="channel"
      @added="onNewComment" :entity-type="entityType" :entity-id="entityId" @hide="replyPanelVisible = false" :parentId="comment.id" />
  </div>
</div>
</template>
<style lang="scss">
.comment {
  position: relative;
  font-size: 1rem;
  margin-bottom: .5em;
  padding-bottom: .5em;
  border-bottom: 1px solid var(--lighten-2);
  &:last-child {
    margin-bottom: 0;
    border-bottom:none;
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
    margin-bottom: .5em;
    font-size: 1.125em;
  }

  &__toolbar {
    display: flex;
    font-size: .625em;
    margin: 0 1em;
    .button {
      margin-right: 1em;
    }
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
    margin-right: .5em;
    @media screen and (max-width: 768px) {
      width: 2.5em;
      height: 2.5em;
    }
  }

  &__author {
    display: flex;
    flex-wrap: wrap;
    flex: 1;
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
    margin-left: .5em;
    @media screen and (max-width: 768px) {
      order: 2;
      width: 100%;
      margin-left: 0;
      margin-top: -.25em;
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

  &__reply-panel {
    font-size: 1rem;

    .comments-panel {
      padding: 1em 0 1em 1em;
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
<script>

import { mapState } from 'vuex';

import { formatPublishDate, formatFullDate } from "@/helpers/dates";

import Rating from '@/components/Rating';
import LikesButton from '@/components/buttons/LikesButton';
import AttachmentsList from '@/components/attachments/AttachmentsList';
import CommentsPanel from '@/components/comments/CommentsPanel';

export default {
  name: 'comment',
  computed: {
    ...mapState('auth', ['loggedIn']),
  },
  components: {
    AttachmentsList,
    Rating,
    CommentsPanel,
    LikesButton
  },

  props: {
    channel: Object,
    canWrite: {
      type: Boolean,
      required: false,
      default: true
    },
    data: {
      type: Object,
      required: true
    },
    entityType: String,
    entityId: Number,
  },
  data() {
    return {
      deleted: false,
      replyPanelVisible: false,
      editPanelVisible: false,
      comment: this.data,
      loadingChildren: false,
    }
  },
  methods: {
    loadChildren() {
      this.loadingChildren = true;
      const afterId = this.comment.children.length ? this.comment.children[this.comment.children.length - 1].id : null;
      this.$api.get(`/comments/${this.comment.id}/children${afterId ? `?after_id=${afterId}` : ''}`).then(children => {
        this.comment.children = [...this.comment.children || [], ...children];
        this.loadingChildren = false;
      })
    },
    formatPublishDate,
    formatFullDate,
    onCommentEdit(comment) {
      if (this.comment.children) {
        comment.children = JSON.parse(JSON.stringify(this.comment.children));
      }
      this.comment = comment;
      this.editPanelVisible = false;
    },
    onNewComment(comment) {
      this.replyPanelVisible = false;
      this.comment.children.push(comment);
    },
    restoreComment() {
      this.$api.post(`/comments/${this.comment.id}/restore`).then(() => {
        this.deleted = false;
      })
    },
    deleteComment() {
      this.$api.delete(`/comments/${this.comment.id}`).then(res => {
        this.deleted = true;
      });
    },
  }
}
</script>
