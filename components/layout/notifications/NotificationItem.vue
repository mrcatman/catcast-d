<template>
  <div class="notification-item" :class="{'notification-item--unread': !data.is_read}" >

    <div class="notification-item__restore" v-if="deleted">
      <span class="notification-item__restore__title">{{$t('notifications.deleted')}}</span>
      <c-button @click="restoreNotification()">{{$t('notifications.restore')}}</c-button>
    </div>

    <c-list-item small :picture="data.picture" :picture-square="true" :to="data.url">
      <template slot="captions">
         <c-translated-message tag="div" :message="data.title" class="list-item__title"></c-translated-message>
         <div class="list-item__text">{{data.text}}</div>
         <div class="list-item__under-title">{{formatPublishDate(data.created_at)}}</div>
      </template>
      <template slot="buttons">
        <c-button flat rounded icon-only icon="close" @click="deleteNotification()" />
      </template>
    </c-list-item>

  </div>
</template>
<style lang="scss" scoped>
.notification-item {
  position: relative;
  &--unread {
    background-color: var(--lighten-2);
    border-left: .125em solid var(--active-color);
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
    background: var(--darken-5);

    &__title {
      font-size: 1.125em;
      margin-right: 1em;
    }
  }
}
</style>
<script>
import { formatPublishDate } from '@/helpers/dates';

  export default {
    data() {
      return {
        deleted: false
      }
    },
    methods: {
      restoreNotification() {
        this.$api.post(`notifications/${this.data.id}/restore`).then(() => {
          this.deleted = false;
        })
      },
      deleteNotification() {
        this.$api.delete(`notifications/${this.data.id}`).then(() => {
          this.deleted = true;
        })
      },
      formatPublishDate
    },
    props: {
      data: {
        type: Object,
        required: true
      }
    }
  }
</script>
