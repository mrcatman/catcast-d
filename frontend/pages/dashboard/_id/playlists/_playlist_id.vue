<template>
    <c-box class="dashboard-page__playlist-editor">
      <template slot="title">
        {{$t('dashboard.playlists.edit')}}
        <c-button target="_blank" :to="playlist.local_url" transparent icon-only icon="arrow_outward">
          <template slot="tooltip">
            <c-tooltip position="bottom-left">{{ $t('global.link') }}</c-tooltip>
          </template>
        </c-button>
      </template>
      <template slot="title_buttons">
        <div class="buttons-row">
          <c-button flat :to="`/dashboard/${channel.id}/playlists`" icon="arrow_back_ios">{{$t('dashboard.playlists.back_to_list')}}</c-button>
        </div>
      </template>
      <template slot="main">
        <c-form v-model="form.data" ref="form" :initial-values="playlist" :method="playlist.id ? 'put' : 'post'" :url="playlist.id ? `/playlists/${playlist.id}` : '/playlists'" :post-data="postData" :use-alerts="true" >
          <c-tabs :data="tabs" v-model="currentTab" />
          <div class="dashboard-page__playlist-editor__content">
            <div v-show="currentTab === 'info'">
              <c-row align="stretch">
                <c-col mobile-full-width class="dashboard-page__playlist-editor__col dashboard-page__playlist-editor__col--scrollable">
                  <c-input v-form-input="'name'" v-form-validate="'required'" :title="$t('dashboard.playlists.name')" />
                  <privacy-status-select v-form-input="'privacy_status'" />
                  <c-text-editor v-form-input="'description'"  :title="$t('dashboard.playlists.description')"/>
                  <c-tags-input v-form-input="'tags'" :title="$t('dashboard.playlists.tags')"/>
                  <c-autocomplete v-form-input="'category'" autocomplete-key="id" autocomplete-value="name" url="categories" :title="$t('dashboard.playlists.category')"/>
                  <c-list-input v-form-input="'links'" :fields="[{id: 'title', name: $t('links_editor.heading'), flexGrow: .5}, {id: 'url', name: $t('links_editor.url')}]" :title="$t('dashboard.playlists.links')" />
                  <privacy-settings class="dashboard-page__playlist-editor__privacy-settings" />
                </c-col>
                <c-col v-if="!isMobile" mobile-full-width :grow="1.5" class="dashboard-page__playlist-editor__col">
                  <playlists-content-editor :channel="channel" :playlist="playlist" v-model="form.media"/>
                </c-col>
              </c-row>
            </div>
            <div v-show="currentTab === 'editor'">
              <playlists-content-editor :channel="channel" :playlist="playlist" v-model="form.media"/>
            </div>
            <div v-show="currentTab === 'design'">
              <c-checkbox :title="$t('dashboard.playlists.use_custom_design')" v-form-input="'use_custom_design'"  />
              <design-editor  v-limit-height="'playlist_editor_design'" v-if="currentTab === 'design' && form.data && form.data.use_custom_design" :channel="channel" :data="playlist" v-model="form.design"/>
            </div>
            <div v-if="currentTab === 'statistics'">
              <statistics-block entity-type="playlists" :entity-id="playlist.id"/>
            </div>
          </div>

        </c-form>
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
<script>
  import statisticsBlock from '@/components/dashboard/statistics/StatisticsViewer.vue';
  import {mapGetters} from 'vuex';
  import DesignEditor from '@/components/dashboard/design/DesignEditor';
  import PlaylistsContentEditor from "@/components/dashboard/playlists/PlaylistsContentEditor";
  import PrivacyStatusSelect from "@/components/dashboard/common/PrivacyStatusSelect";
  import PrivacySettings from "@/components/dashboard/common/PrivacySettings.vue";
  import isMobile from "@/helpers/isMobile";

  export default {
    mounted() {
      this.form.media = this.playlist.media;
    },
    head() {
      return {
        title: this.playlist ? this.playlist.name : this.$t('dashboard.playlists.heading')
      }
    },
    components: {
      PrivacySettings,
      PrivacyStatusSelect,
      PlaylistsContentEditor,

      DesignEditor,
      statisticsBlock,
    },
    props: {
      channel: {
        type: Object,
        required: true
      }
    },
    data() {
      return {
        currentTab: 'info',
        form: {
          data: this.playlist,
          design: {},
          media: []
        },
        statistics: {
          playlist: null,
          visible: false,
        },
        isMobile: isMobile(),
      }
    },
    computed: {
      tabs() {
        return [
          {id: 'info', name: this.$t('dashboard.playlists.tabs.info')},
          this.isMobile ? {id: 'editor', name: this.$t('dashboard.playlists.media.title')} : null,
          {id: 'design', name: this.$t('dashboard.playlists.tabs.design')},
          {id: 'statistics', name: this.$t('statistics.heading')}, // todo: permissions
        ].filter(tab => !!tab);
      },
      postData() {
        return {
          ...this.form?.design,
          media_ids: this.form?.media?.map(media => media.id)
        }
      },
    },

    async asyncData({ app, params }) {
      const playlist = await app.$api.get(`/playlists/${params.playlist_id}`);
      return {
        playlist
      }
    },
  }
</script>
