<template>
  <div class="radio-studio__conference">
    <div class="radio-studio__conference__devices">
      <c-preloader block  v-if="devices.loading" />
      <c-select title="Ваш звук" :options="audioDevicesList" v-model="devices.current"/>
    </div>
    <div class="radio-studio__conference__link">
      <c-preloader block  v-if="conference.loading" />
      <copy-tag v-if="conference.data" :text="conference.data.link"/>
    </div>
    <div class="radio-studio__conference__users">
      <div class="radio-studio__conference__users__empty" v-if="Object.keys(peers).length === 0">Ждем подключений...</div>
      <div class="radio-studio__conference__user" :class="{'radio-studio__conference__user--active': activeUserId == userId}" :key="userId" v-for="(peer, userId) in peers">
        <div class="radio-studio__conference__user__ava" v-if="userData[userId]" :style="{backgroundImage: `url('${userData[userId].avatar}')`}"></div>
        <div class="radio-studio__conference__user__name">{{userData[userId] ? userData[userId].username : userId}}</div>
        <div class="radio-studio__conference__user__buttons">
          <c-button @click="sendRequest(userId)" v-if="!userIsInConference(userId)">Пригласить</c-button>
          <c-button @click="sendRejectRequest(userId)" color="red" v-else>Отключить</c-button>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .radio-studio {
    &__conference {
      &__users {
        &__empty {
          padding: 1em;
        }
      }
      &__user {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1em;
        border-bottom: 1px solid rgba(255, 255, 255, 0.25);
        background: var(--box-color);
        &--active {
          background: rgba(255, 255, 255, .25);
        }
        &__avatar {
          width: 2em;
          height: 2em;
          background-size: contain;
          background-position: center;
          background-repeat: no-repeat;
          margin: 0 .5em 0 0;
        }
        &__name {
          flex: 1;
          text-align: left;
        }
      }
      &__devices {
        padding: .5em;
        position: relative;
      }
      &__link {
        background: var(--box-footer-color);
        padding: .5em;
        position: relative;
      }
    }
  }

</style>
<script>
import copyTag from '@/components/global/copyTag';
import RoomClient from '@/helpers/conference/RoomClient';
export default {
    computed: {
        audioDevicesList() {
            return [
                {
                    value: null,
                    name: 'Без звука'
                },
                ...this.devices.list.map(device => {
                return {
                    value: device.deviceId,
                    name: device.label || `Audio ${audioCount++}`
                }
            })]
        }
    },
    components: {
        copyTag
    },
    beforeDestroy() {
       // this.audio.pause();
       // this.audio.removeAttribute('src');
       // this.audio.load();
    },
    watch: {
        "devices.current"(audioDeviceId) {
            if (audioDeviceId) {
                localStorage.setItem("radioStudio_audioDeviceId", audioDeviceId);
            } else {
                localStorage.removeItem("radioStudio_audioDeviceId");
            }
            this.setHostAudio();
        },
    },
    methods: {
        onActiveSpeaker(id) {
            this.activeUserId = id;
        },
        setHostAudio() {
            if (this.hostMediaStreamSource) {
                this.hostMediaStreamSource.disconnect();
            }
            if (this.devices.current) {
                navigator.mediaDevices.getUserMedia({
                    audio: {
                        deviceId: {
                            exact: this.devices.current
                        }
                    }
                }).then(mediaStream => {
                    this.hostMediaStream = mediaStream;
                    this.hostMediaStreamSource = this.data.audioContext.createMediaStreamSource(mediaStream);
                    this.hostMediaStreamSource.connect(this.data.merger, 0, 0);
                    this.hostMediaStreamSource.connect(this.data.merger, 0, 1);
                    if (this.connected) {
                        this.roomClient.changeStream(mediaStream);
                    }
                    if (!this.firstConnect) {
                        this.connect();
                    }
                })
            } else {
                this.hostMediaStream = null;
                if (this.connected) {
                    this.roomClient.muteMic();
                }
                if (!this.firstConnect) {
                    this.connect();
                }
            }
        },
        getDevices() {

            this.devices.loading = true;
            navigator.mediaDevices.getUserMedia({
                audio: true
            }).then(() => {
                navigator.mediaDevices.enumerateDevices().then((devices) => {
                    this.devices.list = devices.filter(device => device.kind === "audioinput");
                })
                this.devices.loading = false;
                this.setHostAudio();
            }).catch((err) => {
                console.log(err, err.message);
                this.devices.loading = false;
                if (err.message === "Requested device not found") {
                    this.$store.commit('NEW_ALERT', {status: 0, text: 'radio_studio._errors.no_input_devices'});
                } else {
                    this.$store.commit('NEW_ALERT', {status: 0, text: 'radio_studio._errors.unknown_error'});
                }
            });
        },
        sendRejectRequest(id) {
            if (this.peers[id] && this.peers[id].mediaActive) {
                this.peers[id].mediaActive = false;
            }
            if (this.peers[id].audio) {
                this.peers[id].audio.pause();
            }
            this.roomClient.sendRejectRequest({id});
        },
        sendRequest(id) {
            this.roomClient.sendRequest({id});
        },
        userIsInConference(userId) {
            return this.peers[userId] && this.peers[userId].mediaActive;
        },
        async connect() {
            this.firstConnect = true;
            let roomId = this.conference.data.uuid;
            let peerName = "" + this.$store.state.userData.id;
            let token = this.$store.state.token;

            let server = (await this.$api.get('conferences/server')).data.server;
            this.roomClient = new RoomClient({roomId, peerName, token, server});
            this.roomClient.setSoundOnly(true);
            if (this.hostMediaStream) {
                this.roomClient.setPredefinedMediaStream(this.hostMediaStream);
            } else {
                this.roomClient.muteMic();
            }
            this.roomClient.on('add-peer', this.handleAddPeer);
            this.roomClient.on('remove-peer', this.handleRemovePeer);
            this.roomClient.on('add-track', this.handleAddTrack);
            this.roomClient.on('user-data', this.handleUserData);
            this.roomClient.on('join-success', this.handleJoinSuccess);
            this.roomClient.on('join-error', this.handleJoinError);
            this.roomClient.on('active-speaker', this.onActiveSpeaker);
            this.roomClient.connect();
        },
        handleAddPeer({peer}) {
            this.$set(this.peers, peer.name, {
                peer,
                tracks: []
            });
        },
        handleRemovePeer(id) {
            if (this.peers[id].source) {
                this.peers[id].source.disconnect();
            }
            if (this.peers[id].audio) {
                this.peers[id].audio.pause();
            }
            this.$delete(this.peers, id);
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
            this.$set(this.peers[peer.name], 'mediaActive', true);
            this.peers[peer.name].mediaStream = mediaStream;
            this.peers[peer.name].source = this.data.audioContext.createMediaStreamSource(mediaStream);
            //this.peers[peer.name].source.connect(this.data.merger, 0, this.mergerIndex);
            this.peers[peer.name].source.connect(this.data.merger, 0, 0);
            this.peers[peer.name].source.connect(this.data.merger, 0, 1);
            //this.mergerIndex++;
            console.log('TRACK ADDED', this);
            let audio = document.createElement('audio');
            this.peers[peer.name].audio = audio;
            audio.srcObject = mediaStream;
            audio.play();
        },
        handleUserData(user) {
            this.$set(this.userData, user.id, user);
        },
        handleJoinSuccess() {
            this.connected = true;
            this.roomClient.startSendingMedia();
        },
        handleJoinError(e) {
            this.$store.commit('NEW_ALERT', {no_translate: true, status: 0, text: e});
        },
        async init() {
            this.conference.loading = true;
            let data = (await this.$axios.post(`channels/${this.channel.id}/conference`, {audioOnly: true})).data;
            this.conference.loading = false;
            if (data && data.status) {
                this.conference.data = data.data.conference;
                this.getDevices();
            } else {
                this.$store.commit("NEW_ALERT", data);
            }
        }
    },
    mounted() {
        this.init();
    },
    data() {
        return {
            firstConnect: false,
            mergerIndex: 1,
            conference: {
                data: null,
                loading: false
            },
            data: this.value,
            peers: {},
            userData: {},
            audio: null,
            audioContext: null,
            devices: {
                loading: false,
                list: [],
                current: localStorage.radioStudio_audioDeviceId || null
            },
            hostMediaStream: null,
            hostMediaStreamSource: null,
            connected: false,
            activeUserId: null
        }
    },
    props: {
        channel: {
            type: Object,
            required: true
        },
        value: {
            type: Object,
            required: true
        }
    }
  }
</script>
