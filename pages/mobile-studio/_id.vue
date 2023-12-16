<template>
  <div class="mobile-studio__container">
    <div class="mobile-studio" v-if="!error">

      <c-modal :header="$t('studio.not_supported._title')" v-model="notSupported" :showCloseButton="false">
        <div slot="main">
          <div class="modal__text">{{$t('studio.not_supported._text')}}</div>
        </div>
        <div class="modal__buttons" slot="buttons">
          <div class="buttons-row">
            <c-button @click="exit()" >{{$t('studio.not_supported.back')}}</c-button>
          </div>
        </div>
      </c-modal>

      <c-preloader block  v-if="loading" />
      <video pip="false" autoplay muted class="mobile-studio__video" ref="video"></video>

      <div class="mobile-studio__stream-button-container">
        <c-button :loading="streamState === 'STARTING' || streamState === 'STOPPING'" big :class="{'button--green': (streamState !== 'STARTED'), 'button--red': (streamState === 'STARTED' || streamState === 'STOPPING')}" @click="startStopStream()" icon="fa-video">
          {{(streamState === 'STARTED' || streamState === 'STOPPING') ? $t('studio.stop_broadcast') : $t('studio.start_broadcast')}}
        </c-button>
      </div>

      <a class="mobile-studio__button mobile-studio__button--exit" @click="exit()">
        <i class="material-icons">exit_to_app</i>
      </a>
      <a class="mobile-studio__button mobile-studio__button--change-camera" :class="{'mobile-studio__button--disabled': (onlyOneCamera || streamState !== 'NOT_STARTED')}" @click="changeCamera()">
        <i class="material-icons">flip_camera_android</i>
      </a>
      <a class="mobile-studio__button mobile-studio__button--chat" :class="{'mobile-studio__button--active': chatVisible}" @click="chatVisible = !chatVisible">
        <i class="material-icons">chat</i>
      </a>
      <Chat v-show="chatVisible" :channel="channel" class="mobile-studio__chat" :transparent="true"/>
    </div>
    <c-error-page v-else :data="error" />
  </div>
</template>
<script>
  import { formatPublishDate } from '@/helpers/dates.js';
  import Chat from '@/components/Chat';
  import StreamManager from "@/helpers/studio/streammanager";

  export default {
    middleware: 'auth',
    mounted() {
     // if ('MediaRecorder' in window) {
        this.getCamera();
        this.initStreamManager();
      //} else {
      //  this.notSupported = true;
      //}
    },
    watch: {

    },
    computed: {
    },
    methods: {
        initStreamManager() {
            const key = this.keyData.key.stream_key;
            const server = this.keyData.stream_server;
            this.streamManager = new StreamManager({
                wsUrl: server,
                wsQuery: `name=${this.channel.stream_name}&key=${key}`
            });
        },
      startStopStream() {
          if (this.streamState === "NOT_STARTED") {
            this.streamManager.setMediaStream(this.mediaStream);
            this.streamManager.on('error', (e) => {
                console.log(e);
                this.$store.commit('NEW_ALERT', {status: 0, text: 'global.server_error'});
                this.streamState = "NOT_STARTED";
            });
            this.streamManager.start();
            this.streamState = "STARTING";
            this.streamManager.on('started', () => {
                this.streamState = "STARTED";
            })
          } else {
              this.streamManager.stop();
              this.streamState = "STOPPING";
              this.streamManager.on('stopped', () => {
                  this.streamState = "NOT_STARTED";
              })
          }
      },
      changeCamera() {
        if (!this.onlyOneCamera && this.streamState === "NOT_STARTED") {
          this.isFrontCamera = !this.isFrontCamera;
          this.getCamera();
        }
      },
      getCamera() {
        this.loading = true;
        let params = {
          video: {
            facingMode: {exact: (this.isFrontCamera ? "user" : "environment")}
          },
          audio: true,
        };
        if (this.onlyOneCamera) {
          params.video = true;
        }
        console.log(params);
        navigator.mediaDevices.getUserMedia(params).then(stream => {
          console.log('got stream');
          this.loading = false;
          this.mediaStream = stream;
          this.$refs.video.srcObject = stream;
          this.$refs.video.play();
        }).catch( e => {
          console.log(e);
          this.loading = false;
          if (e.name === "OverconstrainedError") {
            if (!this.onlyOneCamera) {
              this.onlyOneCamera = true;
              this.getCamera();
            }
          }
        });
      },
      exit() {
        this.$router.push('/dashboard');
      }
    },
    data() {
      return {
        chatVisible: false,
        mediaStream: null,
        streamManager: null,
        onlyOneCamera: false,
        loading: false,
        isFrontCamera: false,
        notSupported: false,
        streamState: "NOT_STARTED"
      }
    },
    async asyncData({app, params, redirect}) {
      let channelData = (await app.$api.get(`/channels/${params.id}?do_not_count_stat=1`));
      if (channelData.status) {
        if (channelData.data.is_radio) {
          return redirect(`/radio-studio/${params.id}`);
        }
        let channel = channelData.data;
        let id = params.id;
        let permissions = (await app.$axios.post(`/channels/${params.id}/getpermissions`)).data;
        if (Object.keys(permissions).length>0) {
          let keyData = (await app.$axios.post(`/channels/${params.id}/getstreamkey`)).data;
          if (!keyData.status) {
            return {
              error: keyData
            }
          }
          return {
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
      } else {
        return {
          error:
            {
              text: 'errors.404'
            }
        };
      }
    },
    components: {
      Chat,
    },
    head () {
      return {
        title: this.$t('studio._title'),
      }
    }
  }
</script>
<style lang="scss">
  .mobile-studio {
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--main-bg);
    z-index: 100000000;
    &__video {
      height: 100%;
    }
    &__button {
      cursor: pointer;
      font-size: 1.125em;
      color: #ddd;
      &--active {
        color: #fff;
        text-shadow: 0 0 .5em var(--active-color);
      }
      &--disabled {
        color: #555;
      }

      &--chat {
        position: fixed;
        top: 1em;
        left: 4em;
      }
      &--exit {
        position: fixed;
        top: 1em;
        right: 1em;
      }
      &--change-camera {
        position: fixed;
        top: 1em;
        left: 1em;
      }
    }
    &__stream-button-container {
      position: fixed;
      bottom: .5em;
      left: .5em;
      width: calc(100% - 1em);
    }
    &__chat {
      position: fixed!important;
      top: 3.5em!important;
      height: calc(100% - 8em) !important;
    }

  }
</style>
