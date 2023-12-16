<template>
  <div class="radio-player" :class="{'radio-player--default-view': defaultView}" ref="container">
    <c-modal v-model="embedPanel.visible" :header="$t('player.embed._title')">
      <div slot="main">
        <div class="radio-player__embed">{{getEmbedCode}}</div>
        <c-row>
          <c-col>
            <c-input type="number" :title="$t('player.embed.inputs.width')" v-model="embedPanel.inputs.width" :min="1" :max="1920"/>
          </c-col>
          <c-col>
            <c-input type="number" :title="$t('player.embed.inputs.height')"  v-model="embedPanel.inputs.height" :min="1" :max="1080"/>
          </c-col>
        </c-row>
      </div>
    </c-modal>

    <c-modal v-model="requestPanel.visible" :header="$t('radio_player.request_panel._title')">
      <div slot="main">
        <c-response :data="requestPanel.response"/>
        <div class="row">
          <div class="col">
            <c-autocomplete autocomplete-key="id" autocomplete-value="full_title" :url="`radioplaylists/autocomplete/${requestPanel.playlistID}`" v-model="requestPanel.trackName" @getvariant="onRequestPanelGetVariant" @change="onRequestPanelAutocompleteChange" :placeholder="$t('radio_player.request_panel.enter_track_name')" />
          </div>
        </div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button v-if="requestPanel.trackID" :loading="requestPanel.loading" @click="requestTrack()">{{$t('radio_player.request_panel.ok')}}</c-button>
          <c-button flat @click="requestPanel.visible = false">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>


    <div class="radio-player__background" v-show="!defaultView" :style="data.player_background ? 'background:url('+data.player_background+') no-repeat center center;background-size:cover;' : ''"></div>
    <!--<audio style="display:none;" ref="audio_player" v-if="currentTrackData && currentTrackData.playback_url" :src="getPlaybackUrl()"/>-->
    <div class="radio-player__content">
      <div class="radio-player__content__inner">
        <div class="radio-player__content__background"></div>
        <div class="radio-player__info">
          <a class="radio-player__big-button" @click="changePlayerState()">
            <c-preloader v-if="loading || isBuffering"/>
            <i class="material-icons" v-else-if="isPlaying">pause</i>
            <i class="material-icons" v-else>play_arrow</i>
          </a>
          <div class="radio-player__track-container" v-if="isRecordPlayer">
            <div class="radio-player__track">
              <div class="radio-player__track__title">{{record.title}}</div>
              <div class="radio-player__track__author">{{record.author}}</div>
            </div>
          </div>
          <div class="radio-player__track-container" v-else>
            <div class="radio-player__track" v-if="currentTrackData && currentTrackData.is_online">
              <div class="radio-player__track__title">{{currentTrackData.track.title}}</div>
              <div class="radio-player__track__author">{{currentTrackData.track.artist}}</div>
            </div>
            <div v-else class="radio-player__no-broadcast">
              {{$t('radio_stream.no_broadcast')}}
            </div>
          </div>
          <div v-if="isRecordPlayer && duration > 0" class="radio-player__time">
            {{ formatDuration(currentTime) }} /  {{ formatDuration(duration) }}
          </div>
        </div>
      </div>
      <div class="radio-player__progress-bar" ref="progress_bar" @mousedown="onProgressBarClick" v-if="isRecordPlayer">
        <div class="radio-player__progress-bar__inner" :style="{width: progress * 100 + '%'}"></div>
      </div>
      <div class="radio-player__bottom">
        <div class="radio-player__buttons">
          <div class="radio-player__listeners" v-if="currentTrackData && currentTrackData.is_online">
            <i class="fa fa-headphones"></i>
            <span class="radio-player__listeners__count">{{currentTrackData.listeners_count}}</span>
          </div>
          <a @click="showRequestPanel()" class="radio-player__button radio-player__button--request" v-if="currentTrackData && currentTrackData.is_online && currentTrackData.playlist && currentTrackData.playlist.can_accept_requests">
            <span class="radio-player__button__title">{{$t('radio_player.request')}}</span>
            <i class="fa fa-bell"></i>
          </a>
          <a class="radio-player__button radio-player__button--embed" @click="embedPanel.visible = true">
            <span class="radio-player__button__title">{{$t('radio_player.embed')}}</span>
            <i class="fa fa-code"></i>
          </a>
          <a v-show="isRecordPlayer" class="radio-player__button radio-player__button--repeat" :class="{'radio-player__button--active': repeat}" @click="repeat = !repeat">
            <span class="radio-player__button__title">{{$t('radio_player.repeat')}}</span>
            <i class="material-icons">repeat</i>
          </a>
          <div class="radio-player__volume" v-if="(currentTrackData && currentTrackData.is_online) || isRecordPlayer">
            <a class="radio-player__button" @click="playerMuted = !playerMuted">
              <i v-if="playerMuted" class="material-icons">volume_off</i>
              <i v-else class="material-icons">volume_up</i>
            </a>
            <div class="radio-player__volume__bar__container">
              <div class="radio-player__volume__bar__outer">
                <div class="radio-player__volume__bar" ref="bar" @mousedown="onBarClick">
                  <div class="radio-player__volume__bar__inner"  ref="bar_inner" :style="{width:playerVolume*100+'%'}"></div>
                </div>
              </div>
            </div>
          </div>
          <a v-if="embed" href="https://catcast.tv" target="_blank" class="radio-player__logo">
            <svg class="radio-player__logo__picture" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 732.1 265.15">
              <path class="cls-1" d="M256.39,318.7s-20.38,47-72.85,47c-51.65,0-76.18-44.33-76.18-60.17,0-3.64-.82-84.4-.38-98.7a18.42,18.42,0,0,1,.06-2.36c1.41,1.15,32.7,26.31,79.88,26.68,35.49.27,72.65-26.67,72.38-26.45,0,0,0-.24,0-.23-.07,1.16-.08,47.51-.08,47.51,9.7-6.28,42.31-26.08,41.87-27-16.58-35.65-61.55-72.19-117.53-72.19a133,133,0,0,0-133,133c0,73.46,59.43,132.16,132.88,132.16,80,0,120.27-67.49,120.27-78.25" transform="translate(-50.54 -152.74)"/><path class="cls-2" d="M107,207" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M353.53,311.58a30.84,30.84,0,0,1-12-11.62,34.69,34.69,0,0,1,0-33.6,30.49,30.49,0,0,1,12-11.56,35.86,35.86,0,0,1,17.4-4.15,33.34,33.34,0,0,1,15.47,3.49,24.86,24.86,0,0,1,10.54,10.12l-8.79,5.66a19.73,19.73,0,0,0-7.47-6.87,21.35,21.35,0,0,0-9.87-2.29,22.88,22.88,0,0,0-11.26,2.77,19.82,19.82,0,0,0-7.83,7.89,26,26,0,0,0,0,23.6,19.69,19.69,0,0,0,7.83,7.89,22.78,22.78,0,0,0,11.26,2.77,21.23,21.23,0,0,0,9.87-2.29,19.63,19.63,0,0,0,7.47-6.86l8.79,5.54a25.15,25.15,0,0,1-10.54,10.17,32.88,32.88,0,0,1-15.47,3.56A35.46,35.46,0,0,1,353.53,311.58Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M455.4,257.15q7.05,6.51,7.05,19.39v38.53h-11v-8.43a18.29,18.29,0,0,1-8.25,6.81,31.57,31.57,0,0,1-12.7,2.35q-10.73,0-17.16-5.18a17.79,17.79,0,0,1-.3-27.16q6.14-5.11,19.5-5.11h18.31v-2.29q0-7.47-4.34-11.44t-12.76-4a33.88,33.88,0,0,0-11.08,1.87,28.63,28.63,0,0,0-9.15,5l-4.82-8.67a34.89,34.89,0,0,1,11.8-6.08,49.22,49.22,0,0,1,14.57-2.11Q448.36,250.65,455.4,257.15Zm-11.5,46.79a17,17,0,0,0,7-8.37v-8.91H433.07q-14.7,0-14.7,9.87a9,9,0,0,0,3.74,7.59c2.48,1.84,5.94,2.77,10.35,2.77A21.52,21.52,0,0,0,443.9,303.94Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M519.77,311.34a17.23,17.23,0,0,1-6.26,3.31,25.94,25.94,0,0,1-7.71,1.15q-9.63,0-14.93-5.18t-5.3-14.81V260.76H474.73v-9.51h10.84v-14h11.56v14h18.3v9.51h-18.3v34.57q0,5.18,2.59,7.94t7.41,2.77a13.92,13.92,0,0,0,9-3Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M544.15,311.76a32,32,0,0,1-12.88-11.86,34.27,34.27,0,0,1,0-34.44,32,32,0,0,1,12.88-11.86,39.84,39.84,0,0,1,18.61-4.28,36.06,36.06,0,0,1,17.88,4.28,25.34,25.34,0,0,1,11.14,12.22l-14.57,7.83q-5.06-8.91-14.57-8.91a16.5,16.5,0,0,0-12.17,4.82q-4.82,4.81-4.81,13.12t4.81,13.13a16.5,16.5,0,0,0,12.17,4.81q9.63,0,14.57-8.91l14.57,8a25.85,25.85,0,0,1-11.14,12A35.66,35.66,0,0,1,562.76,316,39.84,39.84,0,0,1,544.15,311.76Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M651.87,256.49q8.07,7.17,8.07,21.61v37H642.36V307q-5.31,9-19.75,9a30.59,30.59,0,0,1-13-2.53,19.31,19.31,0,0,1-8.37-7,18.12,18.12,0,0,1-2.89-10.11q0-9,6.81-14.21t21-5.18h14.93q0-6.13-3.73-9.45t-11.2-3.31A32.83,32.83,0,0,0,616,265.88a27,27,0,0,0-8.49,4.4l-6.74-13.13a40.8,40.8,0,0,1,12.7-5.78,57.16,57.16,0,0,1,15.24-2.05Q643.79,249.32,651.87,256.49Zm-16,44.68a12.2,12.2,0,0,0,5.3-6.57V288H628.27q-11.57,0-11.56,7.59a6.76,6.76,0,0,0,2.83,5.72q2.82,2.1,7.76,2.1A16.41,16.41,0,0,0,635.85,301.17Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M682.94,314.05a41,41,0,0,1-12.29-5l6.27-13.48a39.5,39.5,0,0,0,10.47,4.51,43.73,43.73,0,0,0,12,1.75q11.93,0,11.93-5.9c0-1.85-1.09-3.17-3.26-4a48.22,48.22,0,0,0-10-2A98.05,98.05,0,0,1,685,287.14a20.43,20.43,0,0,1-9-5.54q-3.79-4-3.79-11.32a17.71,17.71,0,0,1,3.55-10.9A22.9,22.9,0,0,1,686.13,252a44.31,44.31,0,0,1,16.07-2.65,63.27,63.27,0,0,1,13.67,1.51A38.24,38.24,0,0,1,727.13,255l-6.26,13.37a37.4,37.4,0,0,0-18.67-4.82c-4,0-7,.57-9,1.69s-3,2.57-3,4.34c0,2,1.09,3.41,3.25,4.21a57.58,57.58,0,0,0,10.36,2.29,109.33,109.33,0,0,1,13,2.83,19.52,19.52,0,0,1,8.79,5.48q3.72,4,3.73,11.08a17.14,17.14,0,0,1-3.61,10.71,23.16,23.16,0,0,1-10.54,7.29A47.12,47.12,0,0,1,698.71,316,62.6,62.6,0,0,1,682.94,314.05Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M782.64,311.94a19.1,19.1,0,0,1-6.8,3.07,34,34,0,0,1-8.49,1q-11.57,0-17.88-5.9t-6.32-17.34V266.18h-10V251.73h10V236h18.78v15.77h16.14v14.45H761.93v26.38a8.82,8.82,0,0,0,2.11,6.32,7.82,7.82,0,0,0,6,2.23,12.12,12.12,0,0,0,7.59-2.41Z" transform="translate(-50.54 -152.74)"/>
            </svg>
          </a>
        </div>
        <a :href="'/'+channel.shortname" target="_blank" class="radio-player__station-info">
          <div v-if="channel.logo" class="radio-player__station-info__logo" :style="'background:url('+channel.logo+') no-repeat center center;background-size:contain;'"></div>
          <div class="radio-player__station-info__title">{{channel.name}}</div>
        </a>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .radio-player {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    color: var(--channel-colors-inside-texts);
    &__embed {
      max-width: 36em;
      word-break: break-word;
      padding: .5em;
      background: rgba(255, 255, 255, 0.05);
    }
    &__background {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      background-color: var(--channel-colors-inside-background);
    }
    &__station-info {
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 0 0 1em;
      &__logo {
        width: 3em;
        height: 3em;
      }

      &__title {
        margin: 0 0 0 1em;
        font-weight: 500;
      }
    }


    &__logo {
      opacity: .75;
      transition: opacity .25s;
      flex: 1;
      display: flex;
      justify-content: flex-end;
      @media screen and (max-width: 768px) {
        flex: unset;
        margin: 0 0 0 auto;
        padding: 0 0 0 .5em;
      }
      &:hover {
        opacity: .95;
      }
      &__picture {
        fill: var(--channel-colors-inside-buttons);
        height: 1.25em;
        @media screen and (max-width: 768px) {
          height: 1em;
        }
      }
    }



    &__content {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      height: 100%;
      &__inner {
        position: relative;
        padding: 1em;
        width: calc(100% - 2em);
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
      }
      &__background {
        opacity: .85;
        background: var(--channel-colors-inside-panels);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
      }
    }

    &__button {
      color: var(--channel-colors-inside-buttons) !important;
      cursor: pointer;
      padding: .25em;
      border-radius: .25em;
      transition: all .4s;
      position: relative;
      &--active {
        background: var(--channel-colors-inside-buttons);
      }
      &--repeat {
        margin: 0 0 0 1em;
        padding: .5em;
      }
      &--embed {
        padding: .5em .35em;
      }
      &--request {
        padding: .5em;
        margin: 0 1em 0 0;
      }
      &--logo {
        margin: 0 1em 0 0;
        &__picture {
          fill: var(--channel-colors-inside-buttons);
          height: 1.25em;
        }
      }
      &:hover {
        background: var(--channel-colors-inside-buttons);
      }
      &__title {
        font-size: .65em;
        white-space: nowrap;
        padding: .35em .5em;
        border-radius: .25em;
        position: absolute;
        top: -2em;
        z-index: 10;
        left: 0;
        background: var(--channel-colors-inside-buttons);
        height: 1em;
        line-height: 1;
        display: none;
      }

      &:hover &__title {
        display: block;
      }
    }

      &__buttons {
        margin: 0;
        line-height: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35em;
        flex: 1;
        justify-content: flex-start;
        @media screen and (max-width: 768px) {
          max-width: 100%;
          width: 100%;
        }
        i {
          font-size: 1em;
        }
      }

      &__listeners {
        color: var(--channel-colors-inside-buttons);
        margin: 0 1em 0 0;
        display: flex;
        align-items: center;
        @media screen and (max-width: 768px) {
          margin: 0 .5em 0 0;
        }
        &__count {
          margin: 0 0 0 .5em;
          font-weight: 500;
        }
      }

      &__volume {
        cursor: pointer;
        position: relative;
        display: flex;
        border-radius: 50%;
        transition: all .4s;
        margin: 0 0 0 .75em;
        align-items: center;
        font-size: 1.35em;
        padding: 0;
        @media screen and (max-width: 768px) {
          font-size: 1.25em;
          margin: 0 0 0 .25em;
        }
        @media screen and (max-width: 450px) {
          flex: 1;
        }
        &__icon {
          line-height: 0;
        }

        &__bar {
          user-select: none;
          width: 100%;
          background: var(--channel-colors-inside-buttons);
          margin: 0 .25em;
          height: 0;
          transition: all .4s;
          border-radius: 5em;
          &__inner {
            height: 100%;
            background: var(--channel-colors-inside-buttons);
            border-radius: 50px;
            width: 100%;
          }
          &__container {
            transform-origin: 0 0;
            @media screen and (max-width: 450px) {
              flex: 1;
            }
          }

          &__outer {
            margin: 0 0 0 .5em;
            width: 7em;
            height: 1em;
            border-radius: 100px;
            position: relative;
            left: -.5em;
            display: flex;
            align-items: center;
            justify-content: center;
            @media screen and (max-width: 450px) {
              width: 100%;
            }
          }
        }
        &:hover &__bar {
          height: .35em;
        }
      }
      &__bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 1em 1.25em;
        @media screen and (max-width: 768px) {
          flex-direction: column;
          align-items: flex-start;
        }
      }

      &__no-broadcast {
        font-size: 1.25em;
        font-weight: bold;
        text-align: left;
      }
      &__big-button {
        cursor: pointer;
        transition: all .4s;
        background: linear-gradient(rgba(var(--channel-colors-inside-buttons--rgb), .25), rgba(var(--channel-colors-inside-buttons--rgb), .85));
        color: var(--channel-colors-inside-buttons) !important;
        border-radius: 50%;
        width: 3em;
        height: 3em;
        display: flex;
        align-items: center;
        justify-content: center;
        filter: brightness(1);
        &:hover {
          background: var(--channel-colors-inside-buttons);
          color: var(--channel-colors-inside-buttons) !important;
          filter: brightness(1.25);
          transform: scale(1.1);
        }
        i {
          font-size: 1.5em;
        }
      }

      &__info {
        position: relative;
        z-index: 2;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        width: 100%;
        font-size: 1.25em;
      }
      &__track-container {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0 0 0 1em;
      }
      &__track {
        text-align: left;
        @media screen and (max-width: 768px) {
          font-size: .875em;
        }
        @media screen and (max-width: 450px) {
          font-size: .75em;
        }
        &__title {
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
        }
        &__author {
          font-weight: 500;
        }
      }
      &__progress-bar {
        user-select: none;
        width: 100%;
        height: .75em;
        background: var(--channel-colors-inside-panels);
        cursor: pointer;
        &__inner {
          background: var(--channel-colors-inside-texts);
          height: 100%;
        }
      }
      &--default-view &__content {
        flex-direction: row;
      }
      &--default-view &__content__background {
        display: none;
      }

      &--default-view &__station-info {
        display: none;
      }

      &--default-view &__bottom {
        width: auto;
      }

      &--default-view &__volume {
        width: auto;
      }
      &--default-view &__big-button {
        background: var(--active-color);
      }
      &--default-view &__button:hover {
        background: var(--active-color);
      }

      &--default-view &__volume__bar {
        height: .35em;
        background: rgba(255, 255, 255, 0.1);
      }

      &--default-view &__volume__bar__inner {
        background: #fff;
      }

      &--default-view &__button--embed {
        display: none;
      }

      &--default-view &__listeners {
        margin: 0;
      }
    }
    @keyframes playerVolumeBar {
      0% {
        transform: translateX(5%) scale(0);
      }
      100% {
        transform: translateX(0%) scale(1);
      }
    }
</style>
<script>
  const trackUpdateInterval = 20000;
  import { formatDuration } from '@/helpers/dates';
  export default {
    computed: {
      data() {
        if (this.project) {
          return this.project;
        }
        if (this.channel) {
          return this.channel;
        }
        return null;
      },
      isRecordPlayer() {
        return !!this.record;
      },
      getEmbedCode() {
        let src = this.isRecordPlayer ? `records/player/${this.record.id}` : `player/${this.channel.id}`;
        return `<iframe src="https://catcast.tv/${src}" frameborder="0" allowfullscreen width="${this.embedPanel.inputs.width}" height="${this.embedPanel.inputs.height}"></iframe>`;
      },
    },
    props: {
      embed: {
        type: Boolean,
        required: false,
        default: false
      },
      autoplay: {
        type: Boolean,
        required: false,
        default: false
      },
      defaultView: {
        type: Boolean,
        required: false
      },
      project: {
        type: Object,
        required: false
      },
      record: {
        type: Object,
        required: false
      },
      currentTrack: {
        type: Object,
        required: false
      },
      channel: {
        type: Object,
        required: true,
      }
    },
    watch: {
      playerMuted(isMuted) {
        this.player.volume = isMuted ? 0 : this.playerVolume;
      },
      playerVolume(newVolume) {
        if (newVolume > 0) {
          this.playerMuted = false;
        }
        localStorage.playerVolume = newVolume;
        this.player.volume = newVolume;
      },
      isPlaying(playState) {
        if (this.player) {
          if (playState) {
            if ((this.currentTrackData && this.currentTrackData.is_online) || this.isRecordPlayer) {
            //  this.player.oncanplaythrough = function() {
                this.playerIsActive = true;
            //  };
              this.player.volume = this.playerVolume;
              if (!this.isRecordPlayer) {
               // this.isBuffering = true;
              }
              this.player.play();
              this.$store.commit('HIDE_PLAYER_WITHOUT_UNLOAD');
              this.player.addEventListener('error', (e) => {
                if (e.target && e.target.error && e.target.error.code === 4) {
                  this.$store.commit('NEW_ALERT', this.$t('player.radio_error'));
                  this.isPlaying = false;
                  this.player.pause();
                  this.isBuffering = false;
                  this.playerIsActive = false;
                }
              });
              this.player.addEventListener('ended', (e) => {
                console.log('ended');
                this.isPlaying = false;
                if (this.repeat) {
                  this.player.currentTime = 0;
                  this.$nextTick(() => {
                    this.player.play();
                  })

                }
              });

            }
          } else {
            this.playerIsActive = false;
            this.player.pause();
          }
        }
      }
    },
    data() {
      return {
        requestPanel: {
          playlistID: null,
          visible: false,
          trackName: "",
          trackID: null,
          response: null,
          loading: false,
        },
        repeat: false,
        duration: 0,
        currentTime: 0,
        progress: 0,
        playbackURL: null,
        started: false,
        embedPanel: {
          visible: false,
          inputs: {
            width: 640,
            height: 360,
          }
        },
        getTrackInterval: null,
        player: null,
        isBuffering: false,
        loading: false,
        isPlaying: false,
        playerIsActive: false,
        currentTrackData: this.currentTrack,
        lastUpdateTime: 0,
        playerVolume: (localStorage.playerVolume ? parseFloat(localStorage.playerVolume) : .75),
        playerMuted: false,
        playerLoadTime:null,
      }
    },
    mounted() {
      this.setColors();
      this.player = document.getElementById('audio');
      this.playerLoadTime = Math.floor(new Date().getTime() / 1000);
      if (this.isRecordPlayer) {
        if (this.$store.state.player && this.$store.state.player.track) {
          let track = this.$store.state.player.track;
          if (track.id === this.record.id) {
            if (!this.player.paused) {
              this.isPlaying = true;
              this.$store.commit('HIDE_PLAYER_WITHOUT_UNLOAD');
            }
          }
        }
      } else {
        if (!this.currentTrack) {
          this.loading = true;
          this.getCurrentTrack();
        }

        if (this.$store.state.player && this.$store.state.player.track) {
          let track = this.$store.state.player.track;
          if (track.is_radio && track.channel && track.channel.id === this.channel.id) {
            if (!this.player.paused) {
              this.isPlaying = true;
              this.$store.commit('HIDE_PLAYER_WITHOUT_UNLOAD');
            }
          }
        }
        this.getTrackInterval = setInterval(() => {
          this.getCurrentTrack();
        }, trackUpdateInterval);
      }
    },
    beforeDestroy() {
      if (this.getTrackInterval) {
        clearInterval(this.getTrackInterval);
      }
      if (this.player) {
        if (this.isRecordPlayer) {
          if (this.isPlaying) {
            let track = this.record;
            this.$store.commit('SET_PLAYER_TRACK_WITHOUT_LOAD', {track});
          }
        } else {
          if (this.currentTrackData) {
            if (this.isPlaying) {
              let track = this.currentTrackData.track;
              track.is_radio = true;
              track.channel = this.channel;
              this.$store.commit('SET_PLAYER_TRACK_WITHOUT_LOAD', {track});
            }
          }
        }
      }
    },
    methods: {
      update() {
          this.getCurrentTrack();
          if (this.getTrackInterval) {
              clearInterval(this.getTrackInterval);
          }
          this.getTrackInterval = setInterval(() => {
              this.getCurrentTrack();
          }, trackUpdateInterval);
      },
      requestTrack() {
        this.requestPanel.loading = true;
        this.$axios.post(`radiostream/request/${this.channel.id}`, {
          track_id: this.requestPanel.trackID
        }).then(res => {
          this.requestPanel.loading = false;
          this.requestPanel.response = res.data;
          if (res.data.status) {
            setTimeout(() => {
              this.requestPanel.visible = false;
              this.requestPanel.response = null;
              this.requestPanel.trackName = "";
              this.requestPanel.trackID = null;
            }, 3500)
          }
        })
      },
      onRequestPanelGetVariant(variant) {
        this.requestPanel.trackID = variant.id;
      },
      onRequestPanelAutocompleteChange() {
        this.requestPanel.trackID = null;
      },
      showRequestPanel() {
        this.requestPanel.playlistID = this.currentTrackData.playlist.id;
        this.requestPanel.visible = true;
      },
      formatDuration,
      onTimeUpdate() {
        this.duration = this.player.duration;
        this.currentTime = this.player.currentTime;
        this.progress = this.currentTime / this.duration;
      },
      async loadPlaybackURL(playAfterStart = false) {
        let inlinePlayerData = this.$store.state.player;
        if (inlinePlayerData.track && inlinePlayerData.track.id === this.record.id) {
          if (this.isPlaying) {
            this.$store.commit('HIDE_PLAYER_WITHOUT_UNLOAD');
          }
        } else {
          //  this.loadWaveform();
            this.loading = true;
            let data = (await this.$axios.post(`records/${this.record.id}/url`)).data;
            this.loading = false;
            if (data.status) {
              this.playbackURL = data.data.playback_url;
              this.$nextTick(() => {
                this.player.src = this.playbackURL;
                this.player.addEventListener('timeupdate', this.onTimeUpdate);
                if (playAfterStart) {
                  this.isPlaying = true;
                }
              })
            } else {
              this.$store.commit('NEW_NOTIFY', data);
            }
        }
      },
      setColors() {
        if (this.defaultView) {
          return;
        }
        if (!this.data) {
          return;
        }
        this.data.colors.forEach((color,index)=>{
          this.$refs.container.style.setProperty(`--channel-colors-${index}`, color);
        });
        let bigint = parseInt(this.data.colors[6].substring(1), 16);
        let r = (bigint >> 16) & 255;
        let g = (bigint >> 8) & 255;
        let b = bigint & 255;
        let color = `${r}, ${g}, ${b}`;
        this.$refs.container.style.setProperty(`--channel-colors-inside-buttons--rgb`, color);
      },
      getPlaybackUrl() {
        if (!this.currentTrackData.playback_url) {
          return null;
        }
        return this.currentTrackData.playback_url+"?"+this.playerLoadTime;
      },
      onProgressBarClick(e) {
        const onProgressMove = (e) => {
          const rect = this.$refs.progress_bar.getBoundingClientRect();
          const width = rect.width;
          const left = rect.left;
          let percent = ((e.clientX - left) / width);
          if (percent < 0) {
            percent = 0;
          }
          if (percent > 1) {
            percent = 1;
          }
          this.player.currentTime = percent * this.duration;
        };
        onProgressMove(e);
        window.addEventListener('mousemove', onProgressMove);
        window.addEventListener('mouseup', () => {
          window.removeEventListener('mousemove', onProgressMove);
        })
      },
      onBarClick(e) {
        const onMove = (e) => {
          const rect = this.$refs.bar.getBoundingClientRect();
          const width = rect.width;
          const left = rect.left;
          let percent = ((e.clientX - left) / width);
          if (percent < 0) {
            percent = 0;
          }
          if (percent > 1) {
            percent = 1;
          }
          this.playerVolume = percent;
        };
        onMove(e);
        window.addEventListener('mousemove', onMove);
        window.addEventListener('mouseup', () => {
          window.removeEventListener('mousemove', onMove);
        })
      },
      async changePlayerState() {
        if (!this.started) {
          this.started = true;
          if (this.isRecordPlayer) {
            this.loadPlaybackURL(true);
          } else {
            let url = this.getPlaybackUrl();
            if (url) {
              this.isBuffering = true;
              this.player.src = url;
              this.isPlaying = true;
              this.player.play();
              this.player.addEventListener('loadeddata', () => {
                this.isBuffering = false;
              })
            }
          }
        } else {
          if (!this.loading) {
            if (this.isRecordPlayer) {
              this.isPlaying = !this.isPlaying;
            } else {
              let url = this.getPlaybackUrl();
              if (url) {
                this.isPlaying = !this.isPlaying;
              }
            }
            //const time = new Date().getTime();
            //if (!this.isRecordPlayer) {
              //if (this.isPlaying && (!this.currentTrackData.is_online || (time - this.lastUpdateTime > 30000))) {
              //  this.loading = true;
              //  await this.getCurrentTrack();
              //  this.player.src = this.getPlaybackUrl();
              //}
            //}
          }
        }
      },
      async getCurrentTrack() {
        let res = (await this.$api.get(`/radiostream/getcurrenttrack/${this.channel.id}`));
        this.loading = false;
        this.lastUpdateTime = new Date().getTime();
        this.currentTrackData = res.data;
        if (!res.data.is_online) {
          this.isPlaying = false;
        }
        if (this.autoplay && !this.started) {
          this.changePlayerState();
        }
      }
    }
  }
</script>
