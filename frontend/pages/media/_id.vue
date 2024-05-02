<template>
  <channel-layout class="media-layout" :channel="channel" :playlist="playlist" :mobile-sidebar-container="$refs.mobile_sidebar_container">
    <template slot="main">
      <c-box no-padding>
        <template slot="main">
          <div class="channel-layout__player-container channel-layout__player-container--video" ref="player_container">
            <media-player :media="media" :channel="channel" :autoplay="autoplay" @ended="onEnded" />
            <!-- todo: playlist colors -->
          </div>
        </template>
      </c-box>
      <c-box>
        <template slot="main">
          <channel-entity-top-block
            :entity="media"
            entity-type="media"
            :entity-tags-type="media.type_name"
            :channel="channel"
            :statistics="statistics"
          />
        </template>
      </c-box>
      <div ref="mobile_sidebar_container"></div>
      <comments-list entity-type="media" :entity-id="media.id" :entity-uuid="media.uuid" />
    </template>
    <template slot="sidebar">
      <c-box ref="related" no-padding class="media-layout__related">
        <template slot="title">
          <c-tabs v-if="playlistMedia" :data="tabs" v-model="currentTab" />
          <div v-else>{{$t('media.tabs.related')}}</div>
        </template>
        <template slot="main">
          <div v-if="playlist" class="media-layout__playlist">
            <i18n path="media.playlist_name" tag="span" class="media-layout__playlist__name">
              <nuxt-link :to="`/playlists/${playlist.uuid}`" place="playlist">{{playlist.name}}</nuxt-link>
            </i18n>
            <div class="buttons-row">
              <c-button rounded icon-only icon="fa-play" :flat="!autoplay" @click="autoplay = !autoplay">
                <template slot="tooltip">
                  <c-tooltip position="bottom-left">{{$t('media.autoplay')}}</c-tooltip>
                </template>
              </c-button>
              <c-button rounded icon-only icon="shuffle" :disabled="!autoplay" :flat="!random" @click="random = !random">
                <template slot="tooltip">
                  <c-tooltip position="bottom-left">{{$t('media.random')}}</c-tooltip>
                </template>
              </c-button>
            </div>
            <span class="media-layout__playlist__position">{{playlistIndex + 1}} / {{playlistMedia.total}}</span>
          </div>
          <media-list key="related" v-if="currentTab === 'related'" :url="`/media/${media.uuid}/related`" :data="related" :config="listConfig" />
          <media-list ref="playlist" key="playlist" v-else-if="currentTab === 'playlist'" :url="`/playlists/${this.playlist.id}/media`" :data="playlistMedia" :config="{...listConfig, playlistIndex}" />
        </template>
      </c-box>
    </template>
  </channel-layout>
</template>
<script>
import ChannelLayout from "@/components/ChannelLayout";
import MediaPlayer from "@/components/media-player/MediaPlayer";

import ChannelEntityTopBlock from "@/components/channel/EntityTopBlock";
import MediaList from "@/components/MediaList";
import CommentsList from "@/components/comments/CommentsList";
import {formatPublishDate} from "@/helpers/dates";
import isMobile from "@/helpers/isMobile";

const RELATED_MEDIA_COUNT = 10;

export default {
  watch: {
    autoplay(autoplay) {
      localStorage.playlists_autoplay = autoplay ? '1' : '0';
    },
    random(random) {
      localStorage.playlists_random = random ? '1' : '0';
    }
  },
  mounted() {
    if (isMobile()) {
      this.$refs.mobile_sidebar_container.appendChild(this.$refs.related.$el);
    }
  },
  data() {
    return {
      autoplay: localStorage.playlists_autoplay ? localStorage.playlists_autoplay === '1' : true,
      random: localStorage.playlists_random ? localStorage.playlists_random === '1' : false,
    }
  },
  computed: {
    statistics() {
      const statistics = [
        {icon: 'remove_red_eye', value: this.media.views},
        {icon: 'fa-clock', value: formatPublishDate(this.media.created_at, false)}
      ];
      if (this.media.privacy_status_name !== 'public') {
        statistics.push({
          icon: this.media.privacy_status_name === 'private' ? 'locked' : 'list', value: this.$t(`privacy_statuses.${this.media.privacy_status_name}`)
        })
      }
      return statistics;
    },
    nextPlaylistItem() {
      return this.playlistMedia.data.filter(item => item.index_in_playlist === this.playlistIndex + 1)[0];
    },
    tabs() {
      return [
        {id: 'playlist', name: this.$t('media.tabs.playlist')},
        {id: 'related', name: this.$t('media.tabs.related')},
      ]
    },
    listConfig() {
      return {
        noPadding: true,
        canChangeView: false,
        view: 'list-small',
        showParts: {channel: true, tags: false, description: false},
        innerScroll: true,
        hidePaginator: true,
        queryParams: {
          limit: 10
        },
        disableQuerystringUpdate: true
      }
    }
  },
  async asyncData({app, params, query}) {
    const media = (await app.$api.get(`/media/${params.id}`));

    const playlistData = window.__currentPlaylistData;

    const channel = playlistData ? playlistData.channel : (await app.$api.get(`/channels/${media.channel_id}?do_not_count_stat=1`));
    const related = (await app.$api.get(`/media/${media.uuid}/related`));

    if (query.playlist_id && query.index) {
      const playlistIndex = parseInt(query.index);
      const playlistMediaCurrentPage = Math.floor( playlistIndex / RELATED_MEDIA_COUNT) + 1;
      const playlistMedia = (playlistData && playlistData.playlistMedia.data.filter(item => item.index_in_playlist === playlistIndex).length > 0) ? playlistData.playlistMedia : (await app.$api.get(`/playlists/${query.playlist_id}/media?limit=${RELATED_MEDIA_COUNT}&page=${playlistMediaCurrentPage}`));
      const playlistHasMedia = playlistMedia.data.filter(item => item.id === media.id).length > 0;
      if (playlistHasMedia) {
        const playlist = playlistData ? playlistData.playlist : (await app.$api.get(`/playlists/${query.playlist_id}`));
        return {
          media,
          channel,
          related,
          playlist,
          playlistMedia,
          playlistMediaCurrentPage,
          playlistIndex,
          autoplay: true,
          currentTab: 'playlist'
        };
      }
    }
    return {
      media,
      channel,
      related,
      playlist: null,
      playlistMedia: null,
      autoplay: false,
      currentTab: 'related'
    }
  },
  beforeRouteUpdate(to, from, next) {
    if (from.query.playlist_id && from.query.playlist_id === to.query.playlist_id) { // todo: find a more elegant way
      window.__currentPlaylistData = {
        playlist: this.playlist,
        playlistMedia: this.playlistMedia,
        playlistMediaCurrentPage: this.playlistMediaCurrentPage,
        channel: this.channel
      }
     } else {
      window.__currentPlaylistData = undefined;
    }
    next();
  },
  methods: {
    async onEnded() {
      if (this.autoplay && this.playlist && this.playlistIndex < this.playlistMedia.total) {
        if (this.random) {
          const randomIndex = Math.ceil(Math.random() * (this.playlistMedia.total - 1));
          this.$router.push(`/media/${this.nextPlaylistItem.uuid}?playlist_id=${this.playlist.uuid}&index=${randomIndex}`)
          return;
        }
        if (!this.nextPlaylistItem) {
          await this.$refs.playlist.$refs.list.loadMore();
        }
        if (this.nextPlaylistItem) {
          this.$router.push(`/media/${this.nextPlaylistItem.uuid}?playlist_id=${this.playlist.uuid}&index=${this.nextPlaylistItem.index_in_playlist}`)
        }
      }
    },
    formatPublishDate
  },
  components: {
    MediaPlayer,
    CommentsList,
    MediaList,
    ChannelEntityTopBlock,
    ChannelLayout
  }
}
</script>
<style lang="scss" scoped>
.media-layout {
  &__related {
    width: 100%;
  }
  &__playlist {
    background: var(--lighten-1);
    margin: .5em;
    padding: .25em 1em;
    border-radius: .25em;
    display: flex;
    align-items: center;
    justify-content: center;
    &__name {
      flex: 1;
    }
    &__position {
      font-weight: 600;
      margin-left: 1em;
    }
  }
}
.bright .media-layout__playlist {
  background: var(--darken-1);
}
</style>
