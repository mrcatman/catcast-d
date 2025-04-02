<template>
  <div class="dashboard__media-edit">
    <c-box no-padding>
      <template #title>
        {{$t('dashboard.media.edit')}}
        <c-button target="_blank" :to="media.local_url" transparent icon-only icon="arrow_outward">
          <template #tooltip>
            <c-tooltip position="bottom-left">{{ $t('global.link') }}</c-tooltip>
          </template>
        </c-button>
      </template>
      <template #title_buttons>
        <c-button icon="download" download :href="downloadUrl" v-if="downloadUrl">{{ $t('dashboard.media.download') }}</c-button>
        <c-button flat :to="`/dashboard/${media.channel_id}/media${media.folder_id ? '/folder/' + media.folder_id : ''}`">{{$t('global.back')}}</c-button>
      </template>
      <template #main>
        <c-form box  method="put" :initial-values="media" :url="`/media/${media.id}`" :use-alerts="true">
          <c-tabs small :data="tabs" v-model="currentTab" v-show="tabs.length > 1" />
          <div v-if="currentTab === 'info'" class="dashboard__media-edit__section">
            <c-row align="start">
              <c-col mobile-full-width>
                <div class="dashboard__media-edit__player">
                  <media-player :media="media" :channel="channel"  />
                </div>
                <div class="vertical-delimiter"></div>
                <c-picture-uploader big :proportion="16/9" :title="$t('dashboard.media.thumbnail')" v-model="values.thumbnail" :errors="errors.thumbnail"  />
                <privacy-status-select v-model="values.privacy_status" :errors="errors.privacy_status" />

              </c-col>
              <c-col mobile-full-width>
                <c-input :title="$t('dashboard.media.title')" v-model="values.title" :errors="errors.title" />
                <c-text-editor :title="$t('dashboard.media.description')" v-model="values.description" :errors="errors.description" />
                <c-autocomplete v-model="values.category" :errors="errors.category" autocomplete-key="id" autocomplete-value="name" url="categories" :title="$t('dashboard.media.category')"/>
                <c-tags-input v-model="values.tags" :errors="errors.tags" :title="$t('dashboard.media.tags')"/>
                <c-select multiple :options="playlistsOptions" :title="$t('dashboard.media.playlists')" v-model="values.playlist_ids" :errors="errors.playlist_ids"/>
                <privacy-settings can-disable-rating />
                <!-- todo: audio metadata (maybe) -->
              </c-col>
            </c-row>
          </div>
          <div v-else-if="currentTab === 'statistics'" class="dashboard__media-edit__section">
            <statistics-block entity-type="media" :entity-id="media.id"/>
          </div>
        </c-form>
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
      ::v-deep .media-player__container {
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
<script>
  import MediaPlayer from "@/components/media-player/MediaPlayer";
  import PrivacyStatusSelect from "@/components/dashboard/common/PrivacyStatusSelect";
  import PrivacySettings from "@/components/dashboard/common/PrivacySettings.vue";
  import statisticsBlock from "@/components/dashboard/statistics/StatisticsViewer.vue";
  export default {
    computed: {
      playlistsOptions() {
        return this.playlists.map(playlist => {
          return {
            name: playlist.name,
            value: playlist.id
          }
        })
      },
      downloadUrl() {
        return this.media.files.filter(file => !!file.download_url)[0]?.download_url;
      },
      tabs() {
        const tabs = [
          {id: 'info', name: this.$t('dashboard.media.info')}
        ];
        if (this.media.permissions.can_view_statistics) {
          tabs.push({id: 'statistics', name: this.$t('statistics.heading')});
        }
        return tabs;
      },
    },
    async asyncData({ app, params, redirect }) {
      const media = await app.$api.get(`/media/${params.media_id}?load_permissions=1`);
      if (!media.permissions.can_edit) {
        return redirect(`/dashboard/${params.id}/media`);
      }
      const playlists = await app.$api.get(`/channels/${params.id}/playlists/all`);
      return {
        media,
        playlists
      }
    },
    data() {
      return {
        currentTab: 'info',
      }
    },
    head() {
      return {
        title: this.media ? this.media.title : this.$t('dashboard.media.heading')
      }
    },
    components: {
      statisticsBlock,
      PrivacySettings,
      PrivacyStatusSelect,
      MediaPlayer
    },
    props: {
      channel: {
        type: Object,
        required: true
      }
    },
  }
</script>
