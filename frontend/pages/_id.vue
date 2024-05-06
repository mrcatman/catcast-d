<template>
<channel-layout :channel="channel">
  <template slot="main">
    <c-box no-padding v-show="!playerDetached">
      <template slot="main">
        <div class="channel-layout__player-container" ref="player_container" :class="{'channel-layout__player-container--video': !channel.is_radio, 'channel-layout__player-container--radio': channel.is_radio}">
          <radio-player :currentTrack="currentTrack" :channel="channel" v-if="channel.channel_type === 'radio'" class="channel-layout__player" />
        </div>
      </template>
    </c-box>

    <c-box no-padding>
      <template slot="main">
        <channel-page-top-block :channel="channel" :activeBroadcast="activeBroadcast" />
      </template>
    </c-box>

    <c-box no-padding>
      <template slot="main">
        <c-tabs :data="tabs" v-model="currentTab"/>
        <channel-page-tab-info :channel="channel" v-if="loadedTabs.info" v-show="currentTab === 'info'"/>
        <channel-page-tab-team :channel="channel" v-if="loadedTabs.team" v-show="currentTab === 'team'"/>
        <channel-page-tab-announces :channel="channel" v-if="loadedTabs.announces" v-show="currentTab === 'announces'"/>
        <channel-page-tab-media :channel="channel" v-if="loadedTabs.media" v-show="currentTab === 'media'"/>
        <channel-page-tab-playlists :channel="channel" v-if="loadedTabs.playlists" v-show="currentTab === 'playlists'"/>

      </template>
    </c-box>

    <comments-list entity-type="channels" :entity-id="channel.id" v-if="loadedTabs.info" v-show="currentTab === 'info'"  />

  </template>
  <template slot="sidebar">
    <chat class="channel-layout__chat" :channel="channel"/>
  </template>
</channel-layout>
</template>
<script>
import ChannelLayout from "@/components/ChannelLayout";

import MediaPlayer from '@/components/media-player/MediaPlayer';
import Chat from '@/components/Chat';

import ChannelPageTabTeam from '@/components/channel/Team.vue';
import ChannelPageTabMedia from '@/components/channel/Media.vue';
import ChannelPageTabPlaylists from '@/components/channel/Playlists.vue';
import ChannelPageTabAnnounces from '@/components/channel/Announces.vue';
import ChannelPageTabInfo from '@/components/channel/Info.vue';
import ChannelPageTopBlock from '@/components/channel/TopBlock.vue';
import ChannelPageComments from '@/components/channel/Comments.vue';

import subscribeButton from '@/components/buttons/SubscribeButtonOld.vue';

import {CHANNEL_TYPE_TV} from "@/constants/entity-types";
import CommentsList from "@/components/comments/CommentsList.vue";

export default {
  components: {
    MediaPlayer,
    Chat,
    CommentsList,
    ChannelLayout,
    ChannelPageTopBlock,
    ChannelPageComments,
    ChannelPageTabTeam,
    ChannelPageTabMedia,
    ChannelPageTabPlaylists,
    ChannelPageTabAnnounces,
    ChannelPageTabInfo,
    subscribeButton,
  },
  async asyncData({app, params}) {
    const channel = await app.$api.get(`/channels/${params.id}?find_by=shortname`);
    const activeBroadcast = await app.$api.get(`/channels/${channel.id}/broadcasts/active`);
    return {
      channel,
      activeBroadcast
    };
  },
  watch: {
    $route() {
      if (Object.keys(this.$route.query).length === 0) {
        this.currentTab = "info";
      }
    },
    currentTab(tab) {
      this.loadedTabs[tab] = true;
      if (tab === 'media' || tab === 'playlists') {
        this.detachPlayer();
      } else {
        this.attachPlayer();
      }
    }
  },
  head() {
    return {
      title: this.channel.name
    }
  },
  data() {
    return {
      playerDetached: true,
      currentTab: 'info',
      loadedTabs: {
        info: true
      },
    }
  },
  computed: {
    tabs() {
      return [
        {id: 'info', name: this.$t('channel.tabs.info')},
        {id: 'team', name: this.$t('channel.tabs.team')},
        {id: 'announces', name: this.$t('channel.tabs.announces')},
        {id: 'media', name: this.$t(`channel.tabs.${this.channel.type_name === CHANNEL_TYPE_TV ? 'videos' : 'audio'}`)},
        {id: 'playlists', name: this.$t('channel.tabs.playlists')},
      ];
    },
  },
  mounted() {
   this.attachPlayer();
  },
  beforeDestroy() {
    this.$echo.leave(`App.Channel.${this.channel.id}`);
    if (this.$store.state.players.list['tv_' + this.channel.id]) {
      if (this.$store.state.players.list['tv_' + this.channel.id].is_playing) {
        this.detachPlayer();
      } else {
        this.$store.commit('players/CLOSE_PLAYER', {
          id: 'tv_' + this.channel.id,
        });
      }
    }
  },
  methods: {
    attachPlayer() { // todo: use portals, one player per detach
      if (this.playerDetached && !this.channel.is_radio) {
        if (!this.$store.state.players.list['tv_' + this.channel.id]) {
          this.$store.commit('players/ADD_PLAYER', {
            id: 'tv_' + this.channel.id,
            channel: this.channel,
          });
        }
        this.$nextTick(() => {
          let player = document.getElementById('player_tv_' + this.channel.id);
          this.$refs.player_container.appendChild(player);
          this.$store.commit('players/SET_DETACH_STATE', {id: 'tv_' + this.channel.id, detached: false});
          this.playerDetached = false;
        })
      }
    },
    detachPlayer() {
      if (!this.channel.is_radio && !this.playerDetached) {
        this.playerDetached = true;
        let playerEl = this.$refs.player_container.children[0];
        document.getElementById('video_players').appendChild(playerEl);
        this.$store.commit('players/SET_DETACH_STATE', {id: 'tv_' + this.channel.id, detached: true});
      }
    },
  }
}
</script>
