<template>
  <div class="media-player__top-panel" >
    <div class="media-player__top-panel__background"></div>
    <div class="media-player__top-panel__inner">
      <div class="media-player__top-panel__left">
        <a target="_blank" :href="channel.local_url"  class="media-player__top-panel__logo" v-if="design.logo"  :style="{backgroundImage: `url(${design.logo})`}"></a>
        <div class="media-player__top-panel__captions" v-if="media">
          <a target="_blank" :href="media.local_url" class="media-player__top-panel__name">{{media.title}}</a>
          <a target="_blank" :href="channel.local_url" class="media-player__top-panel__broadcast-name">{{channel.name}}</a>
        </div>
        <div class="media-player__top-panel__captions" v-else>
          <div class="media-player__top-panel__name-container">
            <a target="_blank" :href="channel.local_url"  class="media-player__top-panel__name">{{channel.name}}</a>
            <span class="media-player__top-panel__status media-player__top-panel__status--starting" v-if="startingSoon">{{$t('player.broadcast_will_start_soon')}}</span>
            <span class="media-player__top-panel__status media-player__top-panel__status--offline" v-else-if="!broadcast || broadcast.ended_at">{{$t('player.offline')}}</span>
          </div>

          <div v-if="broadcastName" class="media-player__top-panel__broadcast-name">{{broadcastName}}</div>
        </div>
      </div>
      <div class="media-player__top-panel__right" v-if="!detached">
        <media-player-button  @click="showEmbedModal()" icon="fa-code" :tooltip="$t('player.embed.heading')" tooltipPosition="bottom-left"   />
        <media-player-button @click="showReportModal()" icon="fa-exclamation-triangle" :tooltip="$t('reports.heading')" tooltipPosition="bottom-left"  />
      </div>
      <div class="media-player__top-panel__right" v-else>
        <media-player-button @click="closeDetachedPlayer()" icon="close" />
        <media-player-button @click="attachPlayer()" icon="open_in_new" />
      </div>
    </div>
  </div>
</template>
<script>
import MediaPlayerButton from "@/components/media-player/MediaPlayerButton";
import MediaPlayerEmbed from "@/components/media-player/MediaPlayerEmbed";
import SendReport from "@/components/SendReport";
export default {
  methods: {
    closeDetachedPlayer() {
      this.$store.commit('players/CLOSE_PLAYER', {
        id: this.media ? 'media_' + this.media.id : 'tv_' + this.channel.id,
      });
    },
    attachPlayer() {
      if (this.media) {
        this.$router.push(this.media.local_url);
      } else {
        this.$router.push(this.channel.local_url);
      }
    },
    showEmbedModal() {
      this.$store.commit('modals/showStandardModal', {
        confirm: false,
        title: this.$t('player.embed.heading'),
        component: MediaPlayerEmbed,
        props: {
          channel: this.channel,
          media: this.media,
        }
      })
    },
    showReportModal() {
      this.$store.commit('modals/showStandardModal', {
        confirm: false,
        title: this.$t('reports.heading'),
        component: SendReport,
        props: {
          entity: {
            type: this.media ? 'media' : 'channel',
            id: this.media ? this.media.id : this.channel.id
          },
        }
      })
    }
  },
  props: {
    channel: {
      type: Object,
      required: true
    },
    design: {
      type: Object,
      required: true
    },
    media: Object,
    broadcast: Object,
    detached: Boolean,
    startingSoon: Boolean,
  },
  computed: {
    broadcastName() {
      if (this.broadcast && !this.broadcast.ended_at) {
        return this.broadcast.title;
      }
      return null;
    },
  },
  components: {
    MediaPlayerButton
  }
}
</script>
<style lang="scss" scoped>
.media-player {
  &__top-panel {
    position: absolute;
    z-index: 1;
    width: 100%;
    left: 0;
    top: -3.5em;
    height: 3.5em;
    transition: all .4s;
    .media-player--hovered & {
      top: 0;
    }
    &__background {
      position: absolute;
      left: 0;
      top: 0;
      background: linear-gradient(var(--channel-colors-inside-panels), rgba(var(--channel-colors-inside-panels--rgb), 0));
      width: 100%;
      height: 100%;
      opacity: .85;
    }

    &__inner {
      position: relative;
      display: flex;
      align-items: center;
      height: 100%;
      justify-content: space-between;
      padding: 0 .5em;
    }

    &__logo {
      background-repeat: no-repeat !important;
      background-position: center center !important;
      background-size: contain !important;
      width: 2.5em;
      height: 2.5em;
    }

    &__left {
      text-align: left;
      display: flex;
      align-items: center;
      flex: 1;
      padding-right: 1em;
    }

    &__captions {
      margin-left: .5em;
    }

    &__name-container {
      display: flex;
      align-items: center;
    }
    &__name {
      display: block;
      text-decoration: none;
      font-weight: 600;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      font-size: 1.0625em;
      color: var(--channel-colors-inside-texts);
    }
    &__broadcast-name {
      display: block;
      text-decoration: none;
      font-size: .9375em;
      color: var(--channel-colors-inside-texts);
    }

    &__right {
      display: flex;
      align-items: center;
    }

    &__status {
      display: flex;
      align-items: center;
      margin-left: .75em;
      &:before {
        content: '';
        display: block;
        width: .5em;
        height: .5em;
        border-radius: 1em;
        margin-right: .325em;
      }
      &--offline:before {
        background: var(--lighten-3);
      }
      &--starting:before {
        background: var(--positive-color);
      }
    }
  }
}
</style>
