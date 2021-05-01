<template>
  <div class="sidebar" :class="{'sidebar--opened': opened}">
    <div class="sidebar__block">
      <div class="sidebar__block__title">{{$t('sidebar.favorite_channels')}}</div>
      <ChannelLink class="sidebar__channel" :channel="channel" v-for="channel in channelsList" :key="channel.id">
        <div class="sidebar__channel__logo" v-if="channel.logo" :style="{backgroundImage: `url(${channel.logo.full_url})`}"></div>
        <div class="sidebar__channel__texts">
          <div class="sidebar__channel__name">{{channel.name}}</div>
          <div class="sidebar__channel__offline" v-if="!channel.current_stream">{{$t('sidebar.channel_is_offline')}}</div>
          <div class="sidebar__channel__stream-name" v-else>{{channel.current_stream.name}}</div>
        </div>
      </ChannelLink>
    </div>
    <div class="sidebar__block">
      <div class="sidebar__block__title">{{$t('sidebar.favorite_users')}}</div>
      <ChannelLink v-if="channel.current_stream && channel.current_stream.broadcaster" class="sidebar__channel" :channel="channel" v-for="channel in channelsList" :key="channel.id">
        <div class="sidebar__channel__logo" v-if="channel.current_stream.broadcaster.avatar" :style="{backgroundImage: `url(${channel.current_stream.broadcaster.avatar.full_url})`}"></div>
        <div class="sidebar__channel__texts">
          <div class="sidebar__channel__name">{{channel.current_stream.broadcaster.activitypub_handle}}</div>
          <div class="sidebar__channel__sub">{{$t('sidebar.user_channel')}} <strong>{{channel.name}}</strong></div>
          <div class="sidebar__channel__offline" v-if="!channel.current_stream">{{$t('sidebar.user_is_offline')}}</div>
          <div class="sidebar__channel__stream-name" v-else>{{channel.current_stream.name}}</div>
        </div>
      </ChannelLink>
    </div>
  </div>
</template>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { Watch } from 'vue-property-decorator'
import Channel from '~/types/Channel'
import { ChannelsGetFavorite } from '~/api/modules/channels'
import ChannelLink from '~/components/layout/ChannelLink.vue';

@Component({components: {ChannelLink}})
export default class Sidebar extends Vue {

  loaded: boolean = false;

  get opened() {
    return this.$accessor.modules.sidebar.opened;
  }

  channelsList: Array<Channel> = [];
  userChannelsList: Array<Channel> = [];

  @Watch('opened')
  onOpenedChange(isOpened: boolean) {
    if (isOpened && !this.loaded) {
      this.load();
    }
  }

  async load() {
    const data = await ChannelsGetFavorite();
    this.loaded = true;
    this.channelsList = data.channels;
    this.userChannelsList = data.userChannels;
  }

  mounted() {
    if (this.opened) {
      this.load();
    }
  }

}
</script>
<style lang="scss">
  .sidebar {
    width: 24em;
    height: 100%;
    background: var(--sidebar-bg);
    transition: all .25s;
    display: none;
    flex: unset!important;
    @media screen and (max-width: 768px) {
      z-index: 1000000;
      position: fixed;
      right: 0;
    }
    &--opened {
      display: block;
    }
    &__block {
      border-bottom: 1px solid var(--border-color);
      padding: 1em;
      &__title {
        font-size: 1.0625em;
        font-weight: 500;
      }
    }
    &__channel {
      text-decoration: none;
      display: flex;
      align-items: center;
      font-weight: 300;
      margin: .5em 0;
      height: 3.75em;
      transition: all .25s;
      &:hover {
        opacity: .875;
      }
      &__logo {
        width: 3em;
        height: 3em;
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
        margin: 0 .5em 0 0;
      }

      &__name {
        font-weight: bold;
      }

      &__logo + &__texts {
        max-width: calc(100% - 4em);
      }

      &__stream-name {
        font-size: .875em;
        opacity: .875;
      }
    }
  }
</style>
