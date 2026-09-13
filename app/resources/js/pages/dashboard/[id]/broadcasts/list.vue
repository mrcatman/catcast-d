<template>
  <c-box no-padding>
    <template #main>
      <c-thumbs-list ref="list" :config="listConfig">
        <template #before_filters>
          <c-button color="green" icon="fa-plus" @click="createNewBroadcast()">
            {{ $t('dashboard.broadcast.create') }}
          </c-button>
        </template>
        <template #after_heading>
          <c-tabs v-model="type" :data="types"/>
        </template>
        <template #item="{ item }">
          <broadcast-thumb :data="item" @reload="listRef?.reload()" :dashboard="true"/>
        </template>
      </c-thumbs-list>
    </template>
  </c-box>
</template>
<style lang="scss">

</style>
<script lang="ts" setup>
import BroadcastMetadataEditor from "@/components/dashboard/broadcast/BroadcastMetadataEditor.vue";
import BroadcastThumb from "@/components/thumbs/BroadcastThumb.vue";
import { type Tab } from '@/components/ui/Tabs.vue';
import { type ListConfig } from '@/composables/usePaginatedData';

const { t } = useI18n();
const { request } = useApi();
const { showModal } = useModal();
const { params } = useRoute();

const props = defineProps<{
  channel: Channels.Item;
}>();

useHead(() => ({
  title: t('dashboard.broadcast.list')
}));

const listRef = useTemplateRef('list');

const type = ref<Broadcasts.ListFilter>('all');

const types = computed<Tab[]>(() => [
  { id: 'all', name: t('dashboard.broadcast.types.all') },
  { id: 'planned', name: t('dashboard.broadcast.types.planned') },
  { id: 'finished', name: t('dashboard.broadcast.types.finished') },
]);

const { data: streamData } = await useAsyncData('broadcasts-stream', async () => {
  const key = await request.get('/channels/:id/stream/key', {}, { id: params.id });
  const servers = await request.get('/channels/:id/stream/servers', {}, { id: params.id });
  const activeBroadcast = await request.get('/channels/:id/broadcasts/active', {}, { id: params.id });

  return { key, servers, activeBroadcast };
});

const listConfig = computed<ListConfig<Broadcasts.Item>>(() => ({
  title: t('dashboard.broadcast.list'),
  view: 'list',
  infiniteScroll: true,
  innerScroll: true,
  noPadding: true,
  search: true,
  usePreloadingListItem: true,
  handler: (listParams) => request.get('channels/:id/broadcasts', {
    query: {
      ...listParams,
      show: type.value
    }
  }, {
    id: props.channel.id
  })
}));

const createNewBroadcast = () => {
  showModal<Broadcasts.CreateBody>({
    confirm: true,
    component: BroadcastMetadataEditor,
    buttonColor: '',
    buttonText: t('global.save'),
    title: t('dashboard.broadcast.create'),
    props: { planned: true },
    formValues: {
      tags: []
    },
    fn: async (values) => {
      await request.post('broadcasts', {
        body: {
          ...values,
          channel_id: props.channel.id
        }
      });
      listRef.value?.reload();
    },
  })
}
</script>
