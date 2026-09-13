<template>
  <div class="dashboard__subscribers">
    <c-box no-padding>
      <template #title>
        {{ $t('dashboard.subscribers.heading') }}
      </template>
      <template #main>
        <c-thumbs-list ref="usersList" :config="listConfig">
          <template #item="{ item }">
            <c-list-item :to="`/users/${item.user.id}`" :picture="item.user.avatar" :picture-square="true">
              <template #captions>
                <a class="list-item__title">{{ item.user.username }}</a>
                <div class="list-item__under-title">
                  <span>{{ $t('dashboard.subscribers.subscribed_at', {date: formatDate(item.created_at)}) }}</span>
                </div>
              </template>
            </c-list-item>
          </template>
        </c-thumbs-list>
      </template>
    </c-box>
  </div>
</template>
<script lang="ts" setup>
import { type ListConfig } from '@/composables/usePaginatedData';

const { t } = useI18n();
const { request } = useApi();
const { formatDate } = useDates();

const props = defineProps<{
  channel: Channels.Item;
}>();

useHead(() => ({
  title: t('dashboard.subscribers.heading')
}));

const listConfig = computed<ListConfig<Subscribers.Item>>(() => ({
  view: 'list',
  infiniteScroll: true,
  search: true,
  innerScroll: true,
  noPadding: true,
  handler: (params) => request.get('/channels/:id/subscribers', {
    query: params
  }, {
    id: props.channel.id
  })
}));
</script>
