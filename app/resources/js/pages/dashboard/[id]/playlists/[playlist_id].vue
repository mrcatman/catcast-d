<template>
  <c-box class="dashboard-page__playlist-editor">
    <template #title>
      {{ $t('dashboard.playlists.edit') }}
      <c-button target="_blank" :to="playlist?.local_url" transparent icon-only icon="arrow_outward">
        <template #tooltip>
          <c-tooltip position="bottom-left">{{ $t('global.link') }}</c-tooltip>
        </template>
      </c-button>
    </template>
    <template #title_buttons>
      <div class="buttons-row">
        <c-button flat :to="`/dashboard/${channel.id}/playlists`" icon="arrow_back_ios">
          {{ $t('dashboard.playlists.back_to_list') }}
        </c-button>
      </div>
    </template>
    <template #main>
      <c-form-v2 ref="form" :handler="save" :initial-values="playlist">
        <template #default="{ values, errors }">
          <c-tabs :data="tabs" v-model="currentTab"/>
          <div class="dashboard-page__playlist-editor__content">
            <div v-show="currentTab === 'info'">
              <c-row align="stretch">
                <c-col
                    mobile-full-width
                    class="dashboard-page__playlist-editor__col dashboard-page__playlist-editor__col--scrollable"
                >
                  <c-input v-model="values.name" :errors="errors.name" :title="$t('dashboard.playlists.name')"/>
                  <privacy-status-select v-model="values.privacy_status" :errors="errors.privacy_status"/>
                  <c-text-editor
                      v-model="values.description"
                      :errors="errors.description"
                      :title="$t('dashboard.playlists.description')"
                  />
                  <c-tags-input v-model="values.tags" :errors="errors.tags" :title="$t('dashboard.playlists.tags')"/>
                  <c-autocomplete
                      v-model="values.category"
                      :errors="errors.category"
                      autocomplete-key="id"
                      autocomplete-value="name"
                      url="categories"
                      :title="$t('dashboard.playlists.category')"
                  />
                  <c-list-input
                      v-model="values.links"
                      :errors="errors.links"
                      :fields="[
                        {id: 'title', name: $t('links_editor.heading'), flexGrow: .5},
                        {id: 'url', name: $t('links_editor.url')}
                      ]"
                      :title="$t('dashboard.playlists.links')"
                  />
                  <privacy-settings class="dashboard-page__playlist-editor__privacy-settings"/>
                </c-col>
                <c-col v-if="!isMobileDevice" mobile-full-width :grow="1.5" class="dashboard-page__playlist-editor__col">
                  <playlists-content-editor :channel="channel" :playlist="playlist" v-model="media"/>
                </c-col>
              </c-row>
            </div>
            <div v-show="currentTab === 'editor'">
              <playlists-content-editor :channel="channel" :playlist="playlist" v-model="media"/>
            </div>
            <div v-show="currentTab === 'design'">
              <c-checkbox
                  :title="$t('dashboard.playlists.use_custom_design')"
                  v-model="values.use_custom_design"
                  :errors="errors.use_custom_design"
              />
              <design-editor
                  v-if="currentTab === 'design' && values.use_custom_design"
                  :channel="channel"
                  :data="playlist"
                  v-model="design"
              />
            </div>
            <div v-if="currentTab === 'statistics'">
              <statistics-block entity-type="playlists" :entity-id="playlist?.id"/>
            </div>
          </div>
        </template>
      </c-form-v2>
    </template>
  </c-box>
</template>
<style lang="scss">
.dashboard-page__playlist-editor {
  &__col {
    max-height: calc(100vh - 19em);
    overflow: hidden;

    &--scrollable {
      padding-right: 1em;
      overflow-y: auto;
    }

    @media screen and (max-width: 768px) {
      max-height: unset;
    }
  }

  &__design {
    max-height: calc(100vh - 22em);
    overflow: auto;
    @media screen and (max-width: 768px) {
      max-height: unset;
    }
  }

  &__privacy-settings {
    margin-top: var(--vertical-margin);
  }

  &__content {
    overflow: hidden;
  }
}
</style>
<script lang="ts" setup>
import StatisticsBlock from '@/components/dashboard/statistics/StatisticsViewer.vue';
import DesignEditor from '@/components/dashboard/design/DesignEditor.vue';
import PlaylistsContentEditor from "@/components/dashboard/playlists/PlaylistsContentEditor.vue";
import PrivacyStatusSelect from "@/components/dashboard/common/PrivacyStatusSelect.vue";
import PrivacySettings from "@/components/dashboard/common/PrivacySettings.vue";
import isMobile from "@/helpers/isMobile";
import { type Tab } from '@/components/ui/Tabs.vue';

const { t } = useI18n();
const { request } = useApi();
const { params } = useRoute();

defineProps<{
  channel: Channels.Item;
}>();

const currentTab = ref('info');
const isMobileDevice = isMobile();

const { data: playlist } = await useAsyncData(
    () => `playlist-${params.playlist_id}`,
    () => request.get('/playlists/:id', {}, { id: params.playlist_id })
);

const media = ref<Media.Item[]>(playlist.value?.media ?? []);
const design = ref<Record<string, any>>({});

watch(playlist, (newPlaylist) => {
  media.value = newPlaylist?.media ?? [];
});

const tabs = computed<Tab[]>(() => [
  { id: 'info', name: t('dashboard.playlists.tabs.info') },
  ...(isMobileDevice ? [{ id: 'editor', name: t('dashboard.playlists.media.title') }] : []),
  { id: 'design', name: t('dashboard.playlists.tabs.design') },
  { id: 'statistics', name: t('statistics.heading') }, // todo: permissions
]);

useHead(() => ({
  title: playlist.value?.name || t('dashboard.playlists.heading')
}));

const save = (values: Playlists.SaveBody) => {
  const body: Playlists.SaveBody = {
    ...values,
    ...design.value,
    media_ids: media.value.map(item => item.id)
  };

  return playlist.value?.id
      ? request.put('/playlists/:id', { body }, { id: playlist.value.id })
      : request.post('playlists', { body: body as Playlists.CreateBody });
}
</script>
