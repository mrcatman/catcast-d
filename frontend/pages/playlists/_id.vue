<template>
  <channel-layout class="playlist" :channel="channel" :playlist="playlist">
    <template slot="main">
      <c-box>
        <template slot="main">
          <channel-entity-top-block
            :entity="playlist"
            entity-type="playlists"
            :channel="channel"
            :statistics="[{icon: 'remove_red_eye', value: playlist.views},{icon: 'fa-clock', value: formatPublishDate(playlist.created_at, false)} ]"
            :subscribe="true"
          />
        </template>
      </c-box>


      <c-box no-padding>
        <template slot="main">
          <media-list :data="media" :url="`playlists/${playlist.uuid}/media`"
                      :config="{search: true}">
            <template slot="filters" slot-scope="props">
              <c-select :options="orderOptions" v-model="props.filters.order"></c-select>
            </template>
          </media-list>
        </template>
      </c-box>

      <comments-list entity-type="playlists" :entity-id="playlist.id" :entity-uuid="playlist.uuid"/>
    </template>

    <template slot="sidebar" v-if="related.total > 0">
      <c-thumbs-list ref="list" :data="related" :config="listConfig">
        <template slot="item" slot-scope="props">
          <playlist-thumb :data="props.item" />
        </template>
      </c-thumbs-list>
    </template>
  </channel-layout>
</template>
<script>
import ChannelEntityTopBlock from "@/components/channel/EntityTopBlock";
import AnnounceSubscribeButton from "@/components/buttons/AnnounceSubscribeButton.vue";
import ChannelLayout from "@/components/ChannelLayout";
import MediaList from "@/components/MediaList";
import CommentsList from "@/components/comments/CommentsList";
import { formatPublishDate } from "@/helpers/dates";
import PlaylistThumb from "@/components/thumbs/PlaylistThumb.vue";

export default {
  components: {
    PlaylistThumb,
    ChannelEntityTopBlock,
    CommentsList,
    MediaList,
    ChannelLayout,
    AnnounceSubscribeButton,
  },
  methods: {
    formatPublishDate
  },
  computed: {
    listConfig() {
      return {
        noPadding: true,
        canChangeView: false,
        view: 'list-small',
        innerScroll: true,
        hidePaginator: true,
        disableQuerystringUpdate: true
      }
    }
  },
  async asyncData({app, params}) {
    const playlist = await app.$api.get(`/playlists/${params.id}`);
    const media = (await app.$api.get(`/playlists/${params.id}/media`));
    const related = (await app.$api.get(`/playlists/${params.id}/related`));

    return {
      channel: playlist.channel,
      playlist,
      media,
      related
    }
  },
  head() {
    return {
      title: this.playlist.name
    }
  },
  data() {
    return {
      order: '',
      orderOptions: [
        {
          name: this.$t('media.search.sort.playlist_order'),
          value: ''
        },
        {
          name: this.$t('media.search.sort.playlist_order_reverse'),
          value: 'reverse'
        },
        {
          name: this.$t('media.search.sort.new'),
          value: 'new'
        },
        {
          name: this.$t('media.search.sort.old'),
          value: 'old'
        },
        {
          name: this.$t('media.search.sort.most_watched'),
          value: 'most_watched'
        },
        {
          name: this.$t('media.search.sort.best'),
          value: 'best'
        },
      ],
    }
  },
}
</script>
