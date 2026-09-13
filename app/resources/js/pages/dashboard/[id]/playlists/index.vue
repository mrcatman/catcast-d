<template>
  <c-box no-padding class="dashboard-page__playlists">
    <template #main>
      <c-thumbs-list ref="list" :config="listConfig">
        <template #filters>
          <c-select :options="orderOptions" v-model="order"/>
        </template>
        <template #before_filters>
          <c-button @click="openCreatePlaylistModal()" color="green" icon="add_to_queue">
            {{ $t('dashboard.playlists.add') }}
          </c-button>
        </template>
        <template #item="{ item }">
          <c-list-item
              :to="`/dashboard/${channel.id}/playlists/${item.uuid}`"
              :picture="item.pictures_data.logo ? item.pictures_data.logo.full_url : null"
          >
            <template #captions>
              <div class="list-item__title">
                {{ item.name }}
              </div>
              <div class="list-item__under-title">
                <c-statistics-icons :data="[
                  {icon: 'fa-play', value: item.media_count, margin: true},
                  {icon: 'remove_red_eye', value: item.views},
                  {icon: 'thumb_up', value: item.likes_count, margin: true},
                  {icon: 'fa-clock', value: formatTimeAgo(item.updated_at)}
                ]"/>
              </div>
            </template>
            <template #buttons>
              <c-button v-if="item.can_edit" @click="deleteProject(item)" color="red">
                {{ $t('global.delete') }}
              </c-button>
            </template>
          </c-list-item>
        </template>
      </c-thumbs-list>
    </template>
  </c-box>
</template>
<style lang="scss">
.dashboard-page__playlists {

}
</style>
<script lang="ts" setup>
import PlaylistsCreateModal from "@/components/dashboard/playlists/PlaylistsCreateModal.vue";
import { type ListConfig } from '@/composables/usePaginatedData';

const { t } = useI18n();
const { request } = useApi();
const { formatTimeAgo } = useDates();
const { showModal } = useModal();
const router = useRouter();

const props = defineProps<{
  channel: Channels.Item;
}>();

useHead(() => ({
  title: t('dashboard.playlists.heading')
}));

const listRef = useTemplateRef('list');

const order = ref<'new' | 'old' | 'popular'>('new');

const orderOptions = computed(() => [
  { name: t('media.search.sort.new'), value: 'new' },
  { name: t('media.search.sort.old'), value: 'old' },
  { name: t('media.search.sort.popular'), value: 'popular' },
]);

const listConfig = computed<ListConfig<Playlists.Item>>(() => ({
  view: 'list',
  title: t('dashboard.playlists.heading'),
  infiniteScroll: true,
  search: true,
  innerScroll: true,
  usePreloadingListItem: true,
  noPadding: true,
  handler: (params) => request.get('channels/:id/playlists/manager', {
    query: {
      ...params,
      order: order.value
    }
  }, {
    id: props.channel.id
  })
}));

const openCreatePlaylistModal = () => {
  showModal<Playlists.CreateBody>({
    confirm: true,
    title: t('dashboard.playlists.add'),
    buttonColor: '',
    buttonText: t('global.add'),
    component: PlaylistsCreateModal,
    formValues: {
      name: '',
      privacy_status: 2, // todo: codes
    },
    fn: async (values) => {
      const playlist = await request.post('playlists', {
        body: {
          ...values,
          channel_id: props.channel.id
        }
      });
      router.push(`/dashboard/${props.channel.id}/playlists/${playlist.uuid}`);
    },
  })
}

const deleteProject = (project: Playlists.Item) => {
  showModal({
    confirm: true,
    title: t('dashboard.playlists.delete_project.heading'),
    text: t('dashboard.playlists.delete_project.text'),
    fn: async () => {
      await request.delete('playlists/:id', {}, { id: project.id });
      listRef.value?.reload();
    },
  })
}
</script>
