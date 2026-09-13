<template>
  <div class="notification-item" :class="{'notification-item--unread': !data.is_read}" >

    <div class="notification-item__restore" v-if="deleted">
      <span class="notification-item__restore__title">{{$t('notifications.deleted')}}</span>
      <c-button @click="restoreNotification()">{{$t('notifications.restore')}}</c-button>
    </div>

    <c-list-item small :picture="data.picture" :picture-square="true" :to="data.url">
      <template #captions>
         <c-translated-message tag="div" :message="data.title" class="list-item__title"></c-translated-message>
         <div class="list-item__text">{{data.text}}</div>
         <div class="list-item__under-title">{{formatTimeAgo(data.created_at)}}</div>
      </template>
      <template #buttons>
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
<script lang="ts" setup>
const { request } = useApi();
const { formatTimeAgo } = useDates();

const deleted = ref<boolean>(false);

const props = defineProps<{
  data: Notifications.Item
}>();

const deleteNotification = () => {
  request.delete(`notifications/:id`, {},{
    id: props.data.id
  }).then(() => {
    deleted.value = true;
  })
}

const restoreNotification = () => {
  request.post(`notifications/:id/restore`, {}, {
    id: props.data.id
  }).then(() => {
    deleted.value = false;
  })
}
</script>
