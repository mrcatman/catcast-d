<template>
  <div class="conversation">
    <div class="conversation__inner">
      <div class="centered-block">

        <div v-if="devices.loading" class="conversation__preloader">
          <c-preloader  />
        </div>
        <div class="conversation__container" v-else>
          <div class="conversation__bottom">
            <div class="conversation__me" :class="{'conversation__me--sound-only': soundOnly}">
              <div class="conversation__me__input-select conversation__me__input-select--video" v-if="!soundOnly">
                <span class="conversation__me__input-select__icon" :class="{'conversation__me__input-select__icon--inactive': data.videoDeviceId === null}">
                  <i @click="menusVisible.video = true" class="material-icons">{{data.videoDeviceId !== null ? 'videocam' : 'videocam_off'}}</i>
                </span>
                <c-popup-menu :manual="true" class="popup-menu-top popup-menu-top-left" v-model="menusVisible.video">
                  <c-popup-menu-item :selected="data.videoDeviceId === null" @click="data.videoDeviceId = null">Отключить видео</c-popup-menu-item>
                  <c-popup-menu-item :selected="data.videoDeviceId === device.deviceId" @click="data.videoDeviceId = device.deviceId" v-for="(device, $index) in devices.video" :key="$index">{{device.name}}</c-popup-menu-item>
                </c-popup-menu>
              </div>
              <div class="conversation__me__input-select conversation__me__input-select--audio" >
                <span class="conversation__me__input-select__icon" :class="{'conversation__me__input-select__icon--inactive': data.audioDeviceId === null}">
                  <i @click="menusVisible.audio = true" class="material-icons">{{data.audioDeviceId !== null ? 'mic' : 'mic_off'}}</i>
                </span>
                <c-popup-menu :manual="true" class="popup-menu-top popup-menu-top-left" v-model="menusVisible.audio">
                  <c-popup-menu-item :selected="data.audioDeviceId === null" @click="data.audioDeviceId = null">Отключить звук</c-popup-menu-item>
                  <c-popup-menu-item :selected="data.audioDeviceId === device.deviceId" @click="data.audioDeviceId = device.deviceId" v-for="(device, $index) in devices.audio" :key="$index">{{device.name}}</c-popup-menu-item>
                </c-popup-menu>
              </div>
              <div class="conversation__no-video" v-if="data.videoDeviceId === null && !soundOnly">
                <span class="conversation__no-video__icon">
                  <i class="material-icons">videocam_off</i>
                </span>
              </div>
              <div class="conversation__me__volume">
                <div class="conversation__me__volume__bar" id="my_volume"></div>
              </div>
              <div class="conversation__big-avatar" v-if="soundOnly" :style="{backgroundImage: `url('${$store.state.userData.avatar}'`}"></div>
              <video class="conversation__me__video" v-show="!soundOnly" autoplay muted ref="localVideo"/>
              <div class="conversation__me__connect-status">
                <c-button v-if="status === 'NOT_STARTED' || status === 'CONNECTING'" :loading="status === 'CONNECTING'" @click="connectToRoom()">Подключиться</c-button>
                <span class="conversation__me__connect-status__text" v-if="status === 'WAITING'">Ожидание подтверждения от хоста...</span>
                <c-button v-if="status === 'WAITING' || status === 'CONNECTED'" @click="disconnectFromRoom()">Отключиться</c-button>
              </div>
            </div>
          </div>
          <div class="conversation__instructions">
          </div>
          <!--
            <div class="conversation__local__bottom">

              <span v-else-if="status.connecting">{{$t('video_conference.connecting')}}</span>
              <span v-else-if="status.waiting_for_broadcaster">{{$t('video_conference.waiting_for_broadcaster')}}</span>
              <span v-else-if="status.connecting_to_broadcaster">{{$t('video_conference.connecting_to_broadcaster')}}</span>
              <span v-else-if="status.connected">{{$t('video_conference.connected')}}</span>
            </div>
            -->
          <div class="conversation__remote-peers">
            <div class="conversation__remote-peers__item" :class="{'conversation__remote-peers__item--active': activeUserId == userId}" v-show="peer.tracks.length > 0" :style="{width: (100 / Object.keys(peers).length) + '%'}" v-for="(peer, userId) in peers" :key="userId">
              <div class="conversation__big-avatar" v-if="soundOnly && userData[userId]" :style="{backgroundImage: `url('${userData[userId].avatar}'`}"></div>
              <div class="conversation__remote-peers__item__user-info">
                <div class="conversation__remote-peers__item__ava" v-if="userData[userId]" :style="{backgroundImage: `url('${userData[userId].avatar}')`}"></div>
                <div class="conversation__remote-peers__item__name">{{userData[userId] ? userData[userId].username : userId}}</div>
              </div>
              <video class="conversation__remote-peers__item__video" :ref="'video_'+userId"></video>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .conversation {
    height: 100%;
    &__inner {
      height: 100%;
    }
    &__bottom {
      z-index: 10;
      position: absolute;
      bottom: 4em;
      height: 35vh;
      left: 0;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    &__me {
      margin: 0 auto;
      height: 100%;
      position: relative;
      &--sound-only {
        width: 35vh;
        background: rgba(0, 0, 0, 0.25);
      }
      &__connect-status {
        position: absolute;
        bottom: 0;
        padding: 1em;
        background: rgba(0, 0, 0, 0.5);
        left: 0;
        width: calc(100% - 2em);
      }


      &__video {
        height: 100%;
      }
      &__input-select {
        position: absolute;
        top: .5em;
        cursor: pointer;
        z-index: 100;
        transition: opacity .35s;
        &--video {
          right: 3em;
        }
        &--audio {
          right: .5em;
        }
        &__icon {
          opacity: .75;
          &--inactive {
            opacity: .25;
          }
          &:hover {
            opacity: 1;
          }
        }
      }
      &__volume {
        position: absolute;
        bottom: 4.5em;
        left: 1em;
        width: calc(100% - 2em);
        height: .75em;
        background: #fff;
        z-index: 100;
        border-radius: var(--border-radius);
        overflow: hidden;
        opacity: .25;
        transition: opacity .35s;
        &__bar {
          height: 100%;
          background: var(--active-color);
        }
      }
      &:hover &__volume {
        opacity: .75;
      }
    }

    &__big-avatar {
      position: absolute;
      top: 2em;
      left: 2em;
      width: calc(100% - 4em);
      height: calc(100% - 4em);
      background-size: contain;
      background-position: center;
      background-repeat: no-repeat;
    }
    &__no-video {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--box-color);
      font-size: 5em;

      &__icon {
        opacity: .5;
      }
    }
    &__remote-peers {
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      top: 0;
      left: 0;
      width: 100%;
      background: linear-gradient(45deg, rgba(0, 0, 0, 0.5), transparent);
      height: 45vh;
      &__item {
        text-align: center;
        max-width: 50%;
        height: 100%;
        position: relative;
        flex: 1;
        &__name {
          font-size: 1.125em;
          font-weight: 500;
          margin: 0 0 0 1em;
        }

        &__video {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }
        &__user-info {
          position: absolute;
          bottom: 1em;
          left: 1em;
          display: flex;
          align-items: center;
        }

        &__avatar {
          width: 2.5em;
          height: 2.5em;
          background-size: contain;
          background-position: center center;
          background-repeat: no-repeat;
        }
      }
    }


    &__local__bottom {
      margin: .5em 0 0;
      font-weight: 500;
    }
    &__input-container {
      padding: 1em 0;
    }
    &__conversation {
      display: flex;
      max-width: 80vw;
    }

    &__media-container {
      flex: 1;
      height: auto !important;

      video {
        width: 100%;
      }
    }
  }
</style>
<script>
    import RoomClient from '@/helpers/conference/RoomClient';

    export default {
        middleware: 'auth',
        watch: {
            "data.audioDeviceId"(audioDeviceId) {
                if (audioDeviceId) {
                    localStorage.setItem("conv_audioDeviceId", audioDeviceId);
                } else {
                    localStorage.removeItem("conv_audioDeviceId");
                }
                this.setCamera();
            },
            "data.videoDeviceId"(videoDeviceId) {
                if (videoDeviceId) {
                    localStorage.setItem("conv_videoDeviceId", videoDeviceId);
                } else {
                    localStorage.removeItem("conv_videoDeviceId");
                }
                this.setCamera();
            }
        },
        data() {
            return {
                mediaStream: null,
                userData: {},
                roomClient: null,
                data: {
                    videoDeviceId: this.soundOnly ? null : (localStorage.conv_videoDeviceId || null),
                    audioDeviceId: localStorage.conv_audioDeviceId || null
                },
                lastWorkingDevices: {
                    video: null,
                    audio: null
                },
                devices: {
                    video: [],
                    audio: [],
                    loading: true,
                    error: null
                },
                peers: {},
                status: 'NOT_STARTED',
                menusVisible: {
                    video: false,
                    audio: false
                },
                context: null,
                analyser: null,
                source: null,
                activeUserId: null,
            }
        },
        async asyncData({app, params}) {
            let data = (await app.$api.get(`conferences/${params.id}`));
            if (data.status) {
                return {
                    error: null,
                    conference: data.data.conference,
                    soundOnly: data.data.sound_only
                }
            } else {
                return {
                    error: data
                };
            }
        },
        mounted() {
            this.devices.loading = true;
            navigator.mediaDevices.getUserMedia({
                video: !this.soundOnly,
                audio: true
            }).then(() => {
                this.setDevices();
            }).catch(e =>{
                this.setDevices();
                this.devices.error = e.message;
                this.devices.loading = false;
            });
        },
        methods: {
            onActiveSpeaker(id) {
                console.log("NEW ACTIVE SPEAKER", id);
                this.activeUserId = id;
            },
            startVolumeAnalyser() {
                let context = new AudioContext();
                this.context = context;

                let analyser =  context.createAnalyser();
                this.analyser = analyser;

                let audioTrack = this.mediaStream.getAudioTracks()[0];
                if (!audioTrack) {
                    return;
                }
                let source = context.createMediaStreamSource(this.mediaStream);
                source.connect(analyser);
                this.source = source;

                let javascriptNode = context.createScriptProcessor(2048, 1, 1);
                analyser.smoothingTimeConstant = 0.8;
                analyser.fftSize = 1024;
                analyser.connect(javascriptNode);
                javascriptNode.connect(this.context.destination);
                javascriptNode.onaudioprocess = () => {
                    let array = new Uint8Array(analyser.frequencyBinCount);
                    analyser.getByteFrequencyData(array);
                    let values = 0;
                    let length = array.length;
                    for (let i = 0; i < length; i++) {
                        values += (array[i]);
                    }

                    let average = values / length;
                    document.getElementById('my_volume').style.width = average + '%';
                }
            },
            handleJoinError(error) {
                console.error(error);
                this.status = 'NOT_STARTED';
            },
            handleJoinSuccess() {
                this.status = 'WAITING';
            },
            handleUserData(user) {
                this.$set(this.userData, user.id, user);
            },
            handleAddTrack({peer, track}) {
                if (!this.peers[peer.name]) {
                    return;
                }
                this.peers[peer.name].tracks.push(track);
                let mediaStream = new MediaStream();
                this.peers[peer.name].tracks.forEach(track => {
                    mediaStream.addTrack(track);
                });
                this.$nextTick(() => {
                    let video = this.$refs['video_'+peer.name];
                    if (video && video[0]) {
                        video[0].srcObject = mediaStream;
                        video[0].pause();

                        setTimeout( ()  => {
                            video[0].play();
                        }, 250);
                    }
                })

            },
            handleAddPeer({peer}) {
                this.$set(this.peers, peer.name, {
                    peer,
                    tracks: []
                });
            },
            handleRemovePeer(id) {
                this.$delete(this.peers, id);
            },
            disconnectFromRoom() {
                this.peers = {};
                this.status = "NOT_STARTED";
                this.roomClient.close();
            },
            onStartSendingMedia() {
                this.status = "CONNECTED";
            },
            onStopSendingMedia() {
                this.disconnectFromRoom();
            },
            async connectToRoom() {
                if (this.status === "CONNECTING") {
                    return;
                }
                if (!this.roomClient) {
                     let roomId = this.conference.uuid;
                    let peerName = "" + this.$store.state.userData.id;
                    let token = this.$store.state.token;
                    let server = (await this.$api.get('conferences/server')).data.server;
                    this.roomClient = new RoomClient({server, roomId, peerName, token});
                    this.roomClient.setPredefinedMediaStream(this.mediaStream);
                    this.roomClient.setSoundOnly(this.soundOnly);
                    //this.roomClient.setDevices({
                    //    videoDevice: this.soundOnly ? null : (this.devices.video.filter(device => device.device.deviceId === this.data.videoDeviceId)[0].device),
                    //    audioDevice: this.devices.audio.filter(device => device.device.deviceId === this.data.audioDeviceId)[0].device
                    //})
                    this.roomClient.on('add-peer', this.handleAddPeer);
                    this.roomClient.on('remove-peer', this.handleRemovePeer);
                    this.roomClient.on('add-track', this.handleAddTrack);
                    this.roomClient.on('user-data', this.handleUserData);
                    this.roomClient.on('join-success', this.handleJoinSuccess);
                    this.roomClient.on('join-error', this.handleJoinError);
                    this.roomClient.on('start-sending-media', this.onStartSendingMedia);
                    this.roomClient.on('stop-sending-media', this.onStopSendingMedia);
                    this.roomClient.on('active-speaker', this.onActiveSpeaker);
                }
                this.status = "CONNECTING";
                this.roomClient.connect();
                console.log('connecting', this.roomClient);
            },
            setCamera() {
                if (this.data.videoDeviceId !== null  || this.data.audioDeviceId !== null) {
                    let data = {
                        video: this.data.videoDeviceId !== null && !this.soundOnly ? {
                            deviceId: {
                                exact: this.data.videoDeviceId
                            },
                        } : false,
                        audio: this.data.audioDeviceId !== null ? {
                            deviceId: {
                                exact: this.data.audioDeviceId
                            }
                        } : false
                    };
                    navigator.mediaDevices.getUserMedia(data).then(mediaStream => {
                        if (this.mediaStream) {
                            if (this.source) {
                                this.source.disconnect();
                            }
                            console.log('set source', mediaStream);
                            let source = this.context.createMediaStreamSource(mediaStream);
                            source.connect(this.analyser);
                            this.source = source;
                        }
                        this.mediaStream = mediaStream;
                        this.$refs.localVideo.srcObject = mediaStream;
                        if (this.data.videoDeviceId) {
                            this.lastWorkingDevices.video = this.data.videoDeviceId;
                        }
                        if (this.data.audioDeviceId) {
                            this.lastWorkingDevices.audio = this.data.audioDeviceId;
                        }
                        if (this.status === "CONNECTED") {
                            this.roomClient.changeStream(mediaStream);
                        }
                        if (!this.analyser) {
                            this.startVolumeAnalyser();
                        }
                    }).catch(e => {
                        this.$store.commit('NEW_ALERT', {no_translate: true, status: 0, text: e});
                        this.data.videoDeviceId = this.lastWorkingDevices.video || null;
                        this.data.audioDeviceId = this.lastWorkingDevices.audio || null;
                    })
                } else {
                    this.$refs.localVideo.pause();
                    this.$refs.localVideo.removeAttribute('src');
                    this.$refs.localVideo.load();
                }
            },
            setDevices() {
                navigator.mediaDevices.enumerateDevices().then((devices) => {
                    let audioDevicesList = [];
                    let videoDevicesList = [];
                    let foundDevices = {};
                    let audioCount = 0;
                    let videoCount = 0;
                    devices.forEach(device => {
                        if (!foundDevices[device.deviceId]) {
                            if (device.kind === 'audioinput') {
                                audioDevicesList.push({
                                    device,
                                    deviceId: device.deviceId,
                                    name: device.label || `Audio ${audioCount++}`
                                })
                            }
                            if (device.kind === 'videoinput') {
                                videoDevicesList.push({
                                    device,
                                    deviceId: device.deviceId,
                                    name: device.label || `Video ${videoCount++}`
                                })
                            }
                            foundDevices[device.deviceId] = true;
                        }
                    });
                    this.devices.audio = audioDevicesList;
                    this.devices.video = videoDevicesList;
                    this.devices.loading = false;
                    this.$nextTick(() => {
                        this.setCamera();
                    })
                }).catch(e => {
                    this.devices.error = e.message;
                    this.devices.loading = false;
                });
            },
        }
    }
</script>
