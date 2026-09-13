<template>
  <div class="dashboard__media-edit">
    <c-box no-padding>
      <template #title>
        {{ $t('dashboard.media.edit') }}
        <c-button target="_blank" :to="media.local_url" transparent icon-only icon="arrow_outward">
          <template #tooltip>
            <c-tooltip position="bottom-left">{{ $t('global.link') }}</c-tooltip>
          </template>
        </c-button>
      </template>
      <template #title_buttons>
        <c-button icon="download" download :href="downloadUrl" v-if="downloadUrl">
          {{ $t('dashboard.media.download') }}
        </c-button>
        <c-button
            flat
            :to="`/dashboard/${media.channel_id}/media${media.folder_id ? '/folder/' + media.folder_id : ''}`"
        >
          {{ $t('global.back') }}
        </c-button>
      </template>
      <template #main>
        <c-form-v2 box :handler="save" :initial-values="media">
          <template #default="{ values, errors }">
            <c-tabs small :data="tabs" v-model="currentTab" v-show="tabs.length > 1"/>
            <div v-if="currentTab === 'info'" class="dashboard__media-edit__section">
              <c-row align="start">
                <c-col mobile-full-width>
                  <div class="dashboard__media-edit__player">
                    <media-player :media="media" :channel="channel"/>
                  </div>
                  <div class="vertical-delimiter"></div>
                  <c-picture-uploader
                      big
                      :proportion="16/9"
                      :title="$t('dashboard.media.thumbnail')"
                      v-model="values.thumbnail"
                      :errors="errors.thumbnail"
                  />
                  <privacy-status-select v-model="values.privacy_status" :errors="errors.privacy_status"/>
                </c-col>
                <c-col mobile-full-width>
                  <c-input :title="$t('dashboard.media.title')" v-model="values.title" :errors="errors.title"/>
                  <c-text-editor
                      :title="$t('dashboard.media.description')"
                      v-model="values.description"
                      :errors="errors.description"
                  />
                  <c-autocomplete
                      v-model="values.category"
                      :errors="errors.category"
                      autocomplete-key="id"
                      autocomplete-value="name"
                      url="categories"
                      :title="$t('dashboard.media.category')"
                  />
                  <c-tags-input v-model="values.tags" :errors="errors.tags" :title="$t('dashboard.media.tags')"/>
                  <c-select
                      multiple
                      :options="playlistsOptions"
                      :title="$t('dashboard.media.playlists')"
                      v-model="values.playlist_ids"
                      :errors="errors.playlist_ids"
                  />
                  <privacy-settings can-disable-rating/>
                  <!-- todo: audio metadata (maybe) -->
                </c-col>
              </c-row>
            </div>
            <div v-else-if="currentTab === 'statistics'" class="dashboard__media-edit__section">
              <statistics-block entity-type="media" :entity-id="media.id"/>
            </div>
          </template>
        </c-form-v2>
      </template>
    </c-box>
  </div>
</template>
<style lang="scss" scoped>
.dashboard {
  &__media-edit {
    &__section {
      padding: 1em 1em 0;
    }

    &__player {
      margin-bottom: 1em;
      position: relative;
      padding-top: 60%;

      :deep(.media-player__container) {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
      }
    }
  }
}
</style>
<script lang="ts" setup>
import MediaPlayer from "@/components/media-player/MediaPlayer.vue";
import PrivacyStatusSelect from "@/components/dashboard/common/PrivacyStatusSelect.vue";
import PrivacySettings from "@/components/dashboard/common/PrivacySettings.vue";
import StatisticsBlock from "@/components/dashboard/statistics/StatisticsViewer.vue";
import { type Tab } from '@/components/ui/Tabs.vue';

const { t } = useI18n();
const { request } = useApi();
const { params } = useRoute();

defineProps<{
  channel: Channels.Item;
}>();

const currentTab = ref('info');

const { data } = await useAsyncData(() => `media-${params.media_id}`, async () => {
  const media = await request.get('/media/:id', {
    query: { load_permissions: 1 }
  }, {
    id: params.media_id
  });

  if (!media.permissions.can_edit) {
    await navigateTo(`/dashboard/${params.id}/media`);
    return null;
  }

  const playlists = await request.get('/channels/:id/playlists/all', {}, { id: params.id });

  return { media, playlists };
});

const media = computed(() => data.value?.media as Media.Item);
const playlists = computed(() => data.value?.playlists ?? []);

const playlistsOptions = computed(() => playlists.value.map(playlist => ({
  name: playlist.name,
  value: playlist.id
})));

const downloadUrl = computed(() => {
  return media.value?.files?.find(file => !!file.download_url)?.download_url;
});

const tabs = computed<Tab[]>(() => {
  const list: Tab[] = [
    { id: 'info', name: t('dashboard.media.info') }
  ];
  if (media.value?.permissions?.can_view_statistics) {
    list.push({ id: 'statistics', name: t('statistics.heading') });
  }
  return list;
});

useHead(() => ({
  title: media.value?.title || t('dashboard.media.heading')
}));

const save = (values: Partial<Media.Item>) => {
  return request.put('/media/:id', { body: values }, { id: media.value.id });
}
</script>
