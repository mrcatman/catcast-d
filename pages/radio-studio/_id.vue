<template>
  <div class="radio-studio__outer">
  <div class="radio-studio" v-if="!error">
    <div class="radio-studio__top">
      <div class="radio-studio__audio-manager-container">
        <AudioManager @moveToPlaylist="onMoveToPlaylist" :disableSelection="true" :showPlaylistButtons="hasType(types.TYPE_PLAYLIST)" :showControlButtons="false" :showTopPanel="false" :showEditButtons="false" :drakeName="'studio_tracks'" :channel="channel"/>
      </div>
      <div class="radio-studio__sources">
        <div class="radio-studio__sources__outer">
          <div class="radio-studio__sources__list" v-if="sources.length > 0">
            <div class="radio-studio__source" :key="$index" v-for="(source, $index) in sources">
              <div class="radio-studio__source__name">{{source.name}}</div>
              <AudioPlaylistControl @metadata="setMetadataFromPlaylist" v-if="source.type === types.TYPE_PLAYLIST" v-model="source.data"/>
              <AudioConferenceControl :channel="channel" @metadata="setMetadataFromPlaylist" v-if="source.type === types.TYPE_CONFERENCE" v-model="source.data"/>
              <div class="radio-studio__source__volume">
                <div class="radio-studio__source__volume__bar" :style="{width: source.volume + '%'}"></div>
              </div>
              <div class="radio-studio__source__bottom">
                <div class="radio-studio__source__change-volume">
                  <i class="fa fa-volume-up"></i>
                  <div class="radio-studio__source__change-volume__slider">
                    <c-slider @change="(e) => setVolume(e, source)" v-model="source.volumeGain"/>
                  </div>
                </div>
                <a class="radio-studio__source__bottom__button tooltip-container" @click="toggleSoundOutput(source)" :class="{'radio-studio__source__bottom__button--enabled': source.outputSound}">
                  <i class="fa fa-headphones"></i>
                  <div class="tooltip">{{$t('radio_studio.output_sound')}}</div>
                </a>
                <a @click="disconnectSource(source)" class="radio-studio__source__bottom__button radio-studio__source__bottom__button--delete tooltip-container">
                  <i class="fa fa-times"></i>
                  <div class="tooltip">{{$t('radio_studio.delete_source')}}</div>
                </a>
              </div>
            </div>
         </div>
          <c-nothing-found v-else :icon="'list-ol'" :title="$t('radio_studio.no_sources._title')" :text="$t('radio_studio.no_sources._sub')"/>
        </div>
        <div class="radio-studio__add-source">
          <div class="radio-studio__add-source__buttons" v-if="!sourceToAdd.type">
            <span class="radio-studio__add-source__text">{{$t('radio_studio.add_source')}}</span>
            <div class="radio-studio__add-source__buttons__inner">
              <a @click="addMicrophoneDevice()" class="radio-studio__add-source__button">
                <i class="radio-studio__add-source__button__icon">
                  <i class="fa fa-microphone"></i>
                </i>
                <span class="radio-studio__add-source__button__text">
                  {{$t('radio_studio.sources.microphone')}}
                </span>
              </a>
              <a v-if="!hasType(types.TYPE_PLAYLIST)" @click="addPlaylist()" class="radio-studio__add-source__button">
                <i class="radio-studio__add-source__button__icon">
                  <i class="fa fa-list-ol"></i>
                </i>
                <span class="radio-studio__add-source__button__text">
                  {{$t('radio_studio.sources.playlist')}}
                </span>
              </a>
              <a v-if="!hasType(types.TYPE_CONFERENCE)" @click="addConference()" class="radio-studio__add-source__button">
                <i class="radio-studio__add-source__button__icon">
                  <i class="fa fa-users"></i>
                </i>
                <span class="radio-studio__add-source__button__text">
                  {{$t('radio_studio.sources.conference')}}
                </span>
              </a>
            </div>
          </div>
          <div v-else-if="sourceToAdd.loading" class="radio-studio__add-source__loading">
            <c-preloader />
          </div>
          <div v-else-if="sourceToAdd.error" class="radio-studio__add-source__error">
            {{sourceToAdd.error}}
          </div>
          <div v-else class="radio-studio__add-source__params">
            <div class="radio-studio__add-source__inputs">
              <c-select v-if="sourceToAdd.type === types.TYPE_MICROPHONE" :title="$t('radio_studio.audio_device')" :options="audioDevices" v-model="sourceToAdd.data.audioDeviceId" />
            </div>
            <div class="radio-studio__add-source__bottom">
              <div class="buttons-row">
                <c-button @click="addSelectedSource()">{{$t('global.add')}}</c-button>
                <c-button flat @click="sourceToAdd.type = null">{{$t('global.cancel')}}</c-button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="radio-studio__controls">
        <div class="radio-studio__metadata">
          <div class="box box--with-header">
            <div class="box__header">
              <div class="box__header__title">{{$t('radio_studio.metadata._title')}}</div>
            </div>
            <div class="box__inner">
              <c-input v-model="metadata.data.title" :title="$t('radio_studio.metadata.title')"/>
              <c-input v-model="metadata.data.artist" :title="$t('radio_studio.metadata.artist')"/>
            </div>
            <div class="box__footer">
              <c-button :loading="metadata.loading" @click="saveMetadata()">{{$t('global.save')}}</c-button>
            </div>
          </div>
        </div>
        <div class="radio-studio__stream-control">
          <div class="box box--with-header">
            <div class="box__header">
              <div class="box__header__title">{{$t('radio_studio.stream_control')}}</div>
            </div>
            <div class="box__inner">
              <c-button :disabled="!isBroadcasting && sources.length === 0" big :loading="broadcastStarting" @click="toggleStream()" :class="{'button--red': isBroadcasting}">
                {{!isBroadcasting ? $t('radio_studio.start_broadcast') : $t('radio_studio.stop_broadcast') }}
              </c-button>
            </div>
          </div>
        </div>
        <div class="radio-studio__chat">
          <Chat ref="chat" :channel="channel"/>
        </div>
      </div>
    </div>
    <div class="radio-studio__player-container">
      <RadioPlayerPanel :channel="channel"/>
    </div>
  </div>
  <c-error-page v-else :data="error" />
</div>
</template>
<style lang="scss">
  .radio-studio {
    display: flex;
    flex-direction: column;
    height: 100%;

    @media screen and (max-width: 768px) {
      flex-direction: column;
    }
    &__top {
      height: calc(100% - 6em);
      flex: 1;
      display: flex;
      @media screen and (max-width: 768px) {
        height: auto;
        flex-direction: column;
      }
    }

    &__outer {
      height: 100%;
    }
    &__add-source {
      background: var(--box-footer-color);
      padding: 1em;
      &__buttons {
        display: flex;
        align-items: center;
        &__inner {
          display: flex;
          align-items: center;
        }
        @media screen and (max-width: 768px) {
          flex-direction: column;
        }
      }
      &__text {
        white-space: nowrap;
      }
      &__button {
        background: rgba(255, 255, 255, .05);
        padding: .5em 1em;
        border-radius: .25em;
        margin: 0 0 0 .5em;
        cursor: pointer;
        transition: all .4s;
        display: flex;

        &__text {
          margin: 0 0 0 .5em;
        }
        &:hover {
          background: rgba(255, 255, 255, .1);
        }
      }
      &__inputs {
        padding: 1em 0;
      }
    }
    &__metadata {
      margin: 1em 1em 0;
    }
    &__stream-control {
      margin: 1em 1em 0;
    }
    &__sources {
      flex: 1.75;
      display: flex;
      flex-direction: column;
      &__outer {
        flex: 1;
      }
    }
    &__source {
      margin: 1em;
      background: var(--box-color);
      border-radius: .25em;
      position: relative;

      &__name {
        background: var(--box-header-color);
        color: var(--active-color);
        font-weight: 500;
        padding: .5em 3em .5em .5em;
        border-top-left-radius: .25em;
        border-top-right-radius: .25em;
      }
      &__bottom {
        display: flex;
        background: var(--box-footer-color);
        &__button {
          display: flex;
          align-items: center;
          padding: .5em 1em;
          cursor: pointer;
          transition: all .4s;
          background: rgba(255, 255, 255, .05);
          &:hover {
            background: rgba(255, 255, 255, .1);
          }
          &--enabled {
            background: var(--active-color);
            &:hover {
              background: var(--active-color);
            }
          }
          &--delete {
            border-bottom-right-radius: .25em;
            background: var(--negative-color);
            &:hover {
              background: var(--negative-color);
            }
          }
          &__text {
            margin: 0 0 0 .5em;
          }
        }
      }
      &__change-volume {
        padding: .5em;
        flex: 1;
        display: flex;
        align-items: center;
        border-bottom-left-radius: .5em;
        &__slider {
          flex: 1;
          margin: 0 1em;
        }
      }

      &__volume {
        border-radius: .25em;
        overflow: hidden;
        height: 1em;
        margin: 1em;
        background: rgba(0, 0, 0, .25);
        width: calc(100% - 2em);
        &__bar {
          background: var(--active-color);
          height: 100%;
        }
      }
    }
    &__audio-manager-container {
      background: rgba(0, 0, 0, .1);
      flex: 1;
      .audio-manager {
        height: 100%;
      }
    }
    &__chat {
      flex: 1;
      margin: 1em;
      .chat__main {
        @media screen and (max-width: 768px) {
          min-height: 70vh;
        }
      }
    }
    &__controls {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
  }
</style>
<script>
  import Webcast from '@/helpers/webcast.js';
  import Chat from '@/components/Chat';

  import AudioPlaylistControl from '@/components/radio-studio/AudioPlaylistControl';
  import AudioConferenceControl from '@/components/radio-studio/AudioConferenceControl';
  import AudioManager from '@/components/AudioManager';
  import RadioPlayerPanel from "@/components/RadioPlayerPanel";

  const TYPE_MICROPHONE = 1;
  const TYPE_PLAYLIST = 2;
  const TYPE_CONFERENCE = 3;

  function webAudioTouchUnlock(context)
  {
    return new Promise(function (resolve, reject)
    {
      if (context.state === 'suspended')
      {
        let unlock = function()
        {
          context.resume().then(function()
            {
              document.body.removeEventListener('touchstart', unlock);
              document.body.removeEventListener('touchend', unlock);

              resolve(true);
            },
            function (reason)
            {
              reject(reason);
            });
        };
        document.body.addEventListener('click', unlock, false);
        document.body.addEventListener('touchstart', unlock, false);
        document.body.addEventListener('touchend', unlock, false);
      }
      else
      {
        resolve(false);
      }
    });
  }

  export default {
    head () {
      return {
        title: this.$t('radio_studio._title'),
      }
    },
    middleware: 'auth',
    methods: {
      hasType(type) {
        return this.sources.filter(source => source.type === type).length > 0;
      },
      onMoveToPlaylist(tracks) {
        this.sources.forEach((source, index) => {
          if (source.type === TYPE_PLAYLIST){
            let data = JSON.parse(JSON.stringify(source.data));
            tracks.forEach(track => {
              data.playlist.push(track);
            });
            this.$set(this.sources[index], 'data', data);
          }
        })
      },
      setMetadataFromPlaylist(metadata) {
        this.metadata.data.title = metadata.title;
        this.metadata.data.artist = metadata.artist;
        this.saveMetadataInBackground();
      },
      saveMetadataInBackground() {
        this.$axios.post(`/channels/${this.channel.id}/metadata`, {
          title: this.metadata.data.title,
          artist: this.metadata.data.artist
        }).then( res => {
          this.webcast.sendMetadata(this.metadata.data);
        })
      },
      saveMetadata() {
        this.metadata.loading = true;
        this.$axios.post(`/channels/${this.channel.id}/metadata`, {
          title: this.metadata.data.title,
          artist: this.metadata.data.artist
        }).then( res => {
          this.webcast.sendMetadata(this.metadata.data);
          this.metadata.loading = false;
          this.$store.commit('NEW_ALERT', res.data);
        })
      },
      toggleSoundOutput(source) {
        source.outputSound = !source.outputSound;
      },
      disconnectSource(source) {
        let index = this.sources.indexOf(source);
        console.log(source);
        //source.source.stop();
        source.source.disconnect();
        source.trackGain.disconnect();
        source.controlsNode.disconnect();
        source.passThrough.disconnect();
        source.source = source.trackGain = source.controlsNode = source.passThrough = null;
        this.sources.splice(index, 1);
      },
      setVolume(e, source) {
        source.trackGain.gain.value = e;
      },
      addStream(source, name, type, data) {
        let bufferLength, bufferLog, bufferSize, log10;
        bufferSize = 4096;
        bufferLength = parseFloat(bufferSize) / parseFloat(this.audioCtx.sampleRate);
        bufferLog = Math.log(parseFloat(bufferSize));
        log10 = 2.0 * Math.log(10);
        let controlsNode = this.audioCtx.createScriptProcessor(bufferSize, 2, 2);
        let obj = {
          volumeGain: 100,
          type,
          source,
          name,
          data,
          volume: 0,
          outputSound: false,
        };
        controlsNode.onaudioprocess = (function(_this) {
          return function(buf) {
            var channel, channelData, i, j, k, ref1, ref2, results, rms, volume;
            results = [];
            for (channel = j = 0, ref1 = buf.inputBuffer.numberOfChannels - 1; 0 <= ref1 ? j <= ref1 : j >= ref1; channel = 0 <= ref1 ? ++j : --j) {
              channelData = buf.inputBuffer.getChannelData(channel);
              rms = 0.0;
              for (i = k = 0, ref2 = channelData.length - 1; 0 <= ref2 ? k <= ref2 : k >= ref2; i = 0 <= ref2 ? ++k : --k) {
                rms += Math.pow(channelData[i], 2);
              }
              volume = 100 * Math.exp((Math.log(rms) - bufferLog) / log10);
              obj.volume = volume;
              results.push(buf.outputBuffer.getChannelData(channel).set(channelData));
            }
            return results;
          };
        })(this);


        controlsNode.connect(this.webcast);
        obj.controlsNode = controlsNode;
        let trackGain = this.audioCtx.createGain();
        trackGain.connect(controlsNode);

        let passThrough;
        passThrough = this.audioCtx.createScriptProcessor(256, 2, 2);
        passThrough.onaudioprocess = (function(_this) {
          return function(buf) {
            let channel, channelData, j, ref, results;
            channelData = buf.inputBuffer.getChannelData(channel);

            results = [];
            for (channel = j = 0, ref = buf.inputBuffer.numberOfChannels - 1; 0 <= ref ? j <= ref : j >= ref; channel = 0 <= ref ? ++j : --j) {
              if (obj.outputSound) {
                results.push(buf.outputBuffer.getChannelData(channel).set(channelData));
              } else {
                results.push(buf.outputBuffer.getChannelData(channel).set(new Float32Array(channelData.length)));
              }
            }
            return results;
          };
        })(this);

        passThrough.connect(this.audioCtx.destination);
        trackGain.connect(passThrough);
        obj.passThrough = passThrough;
        source.connect(trackGain);
        obj.trackGain = trackGain;
        this.sources.push(obj)
      },
      addSelectedSource() {
        if (this.sourceToAdd.type === TYPE_MICROPHONE) {
          navigator.mediaDevices.getUserMedia({
            audio: {
              deviceId: {
                exact: this.sourceToAdd.data.audioDeviceId
              }
            }
          }).then(stream => {
            let name = this.audioDevices.filter(device => device.value === this.sourceToAdd.data.audioDeviceId)[0].name;
            let source = this.audioCtx.createMediaStreamSource(stream);
            this.addStream(source, name, TYPE_MICROPHONE, {deviceId: this.sourceToAdd.data.audioDeviceId});
            this.sourceToAdd.type = null;
          }).catch(err => {
            console.log(err);
          })
        } else {
          if (this.sourceToAdd.type === TYPE_PLAYLIST) {

          }
        }
      },
      addConference() {
        let merger = this.audioCtx.createChannelMerger(20);

        this.addStream(merger, "Conference", TYPE_CONFERENCE, {audioContext: this.audioCtx, merger});
        this.sourceToAdd.type = null;
      },
      addPlaylist() {
        let el = document.createElement('audio');
        let playlist = [];
        let source = this.audioCtx.createMediaElementSource(el);
        this.addStream(source, "Playlist", TYPE_PLAYLIST, {el, playlist});
        this.sourceToAdd.type = null;
      },
      addMicrophoneDevice() {

        this.sourceToAdd.type = TYPE_MICROPHONE;
        if (!this.devices) {
          this.sourceToAdd.loading = true;
          navigator.mediaDevices.getUserMedia({
              audio: true
          }).then(stream => {
            navigator.mediaDevices.enumerateDevices().then((devices) => {
              this.devices = devices.filter(device => device.kind === "audioinput");
              this.sourceToAdd.loading = false;
            })
          }).catch((err) => {
            this.sourceToAdd.loading = false;
            console.log(err.message);
            if (err.message === "Requested device not found") {
              this.sourceToAdd.error = this.$t('radio_studio._errors.no_input_devices');
            } else {
              this.sourceToAdd.error = this.$t('radio_studio._errors.unknown_error');
            }
          });
        } else {

        }
      },
      startVolumeAnalyzer() {
        this.analyser =  this.audioCtx.createAnalyser();
        let analyser =  this.analyser;
        let javascriptNode =  this.audioCtx.createScriptProcessor(2048, 1, 1);
        analyser.smoothingTimeConstant = 0.8;
        analyser.fftSize = 1024;
        analyser.connect(javascriptNode);
        javascriptNode.connect(this.audioCtx.destination);
        javascriptNode.onaudioprocess = () => {
          let array = new Uint8Array(analyser.frequencyBinCount);
          analyser.getByteFrequencyData(array);
          let values = 0;
          let length = array.length;
          for (let i = 0; i < length; i++) {
            values += (array[i]);
          }
          let average = values / length;
          this.volume = average;
        }
      },
      async toggleStream() {
        if (!this.isBroadcasting) {
          if (this.sources.length === 0) {
            return;
          }
          this.broadcastStarting = true;
          let timeout = 1;
          let radioStatus = (await this.$api.get(`/radiostream/getstate/${this.channel.id}`)).data.is_online;
          if (!radioStatus) {
            timeout = 5000;
            await this.$axios.post(`/radiostream/setstate/${this.channel.id}`,{state: true});
          }
          setTimeout(() => {
            this.audioCtx.resume();
            let encoder = new Webcast.Encoder.Mp3({
              channels: 2,
              samplerate: 44100,
              bitrate: 128,
            });
            this.encoder = new Webcast.Encoder.Resample({
              encoder: encoder,
              samplerate: this.audioCtx.sampleRate
            });
            let address = this.broadcastAddress;
            this.webcast.connectSocket(this.encoder, address);
            this.isBroadcasting = true;
            this.broadcastStarting = false;
            setTimeout(() => {
              this.webcast.sendMetadata(this.metadata.data);
            }, 5000)
          })
        } else{
          this.webcast.close();
          this.isBroadcasting = false;
        }
      }
    },
    computed: {
      audioDevices() {
        if (!this.devices) {
          return [];
        }
        let sources = this.sources.map(source => source.data.deviceId);
        return this.devices.filter(device => {
          return sources.indexOf(device.deviceId) === -1;
        }).map(device => {
          return {
            value: device.deviceId,
            name: device.label
          }
        })
      },
      broadcastAddress() {
        let server = this.servers.list[0].browser_address;
        server = server.replace("http://", "");
        return `wss://source:${this.keyData.key.stream_key}@${server}`;
      }
    },
    data() {
      return {
        broadcastStarting: false,
        metadata: {
          loading: false,
          data: {
            title: '',
            artist: ''
          }
        },
        webcast: null,
        encoder: null,
        zeroGain: null,
        volume: 0,
        sourceToAdd: {
          type: null,
          data: {},
          loading: false,
          error: null
        },
        devices: null,
        audioCtx: null,
        audioDest: null,
        sources: [],
        isBroadcasting: false,
        types: {
          TYPE_MICROPHONE: TYPE_MICROPHONE,
          TYPE_PLAYLIST: TYPE_PLAYLIST,
          TYPE_CONFERENCE: TYPE_CONFERENCE
        }
      }
    },
    beforeDestroy() {
      if (this.$dragula.$service.drakes.studio_tracks) {
        delete this.$dragula.$service.drakes.studio_tracks;
      }
    },
    created() {
      const service = this.$dragula.$service;
      service.options('studio_tracks', {
        copy: function(el,source) {
          const sourceIsPlaylist = source.classList.contains('radio-studio__playlist__tracks');
          if (sourceIsPlaylist) {
            return false;
          }
          return true;
        },
        accepts: function (el, target, source, sibling) {
          const sourceIsTracksList = source.classList.contains('audio-manager__tracks');
          const targetIsTracksList = target.classList.contains('audio-manager__tracks');
          const sourceIsPlaylist = source.classList.contains('radio-studio__playlist__tracks');
          const targetIsPlaylist = target.classList.contains('radio-studio__playlist__tracks');
          if (sourceIsPlaylist && targetIsPlaylist) {
            return true;
          }
          if (sourceIsTracksList && targetIsPlaylist) {
            return true;
          }
          return false;
        },
        revertOnSpill: true
      })
    },
    async mounted() {
      let context;
      if (typeof webkitAudioContext !== "undefined") {
        context = new webkitAudioContext;
      } else {
        context = new AudioContext;
      }
      this.audioCtx = context;
      //this.startVolumeAnalyzer();

      this.webcast = context.createWebcastSource(4096, 2);
      this.webcast.connect(context.destination);
      if (this.channel) {
        this.metadata.data.title = this.channel.program_name || "";
        this.metadata.data.artist = this.channel.program_artist || "";
      }
      webAudioTouchUnlock(context).then(() => {
        console.log('unlocked');
      });
    },
    components: {
      RadioPlayerPanel,
      Chat,
      AudioManager,
      AudioPlaylistControl,
      AudioConferenceControl
    },
    async asyncData({app, params, redirect}) {
      let channelData = (await app.$api.get(`/channels/${params.id}?do_not_count_stat=1`));
      if (channelData.status) {
        if (!channelData.data.is_radio) {
          return redirect(`/studio/${params.id}`);
        }
        let channel = channelData.data;
        let id = params.id;
        let permissions = (await app.$axios.post(`/channels/${params.id}/getpermissions`)).data;
        if (Object.keys(permissions).length > 0) {
          let servers = (await app.$api.get(`/channels/${params.id}/getserverslist`)).data;
          let keyData = (await app.$axios.post(`/channels/${params.id}/getstreamkey`)).data;
          if (!keyData.status) {
            return {
              error: keyData
            }
          }

          return {
            servers,
            keyData,
            error: false,
            channel,
            id,
            permissions
          };
        } else {
          return {
            error:
              {
                text: 'errors.403'
              }
          };
        }
      }
    },
  }
</script>
