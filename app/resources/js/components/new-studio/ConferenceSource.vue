<template>
  <div class="conference-source" ref="container">
    <BaseSource class="conference-source__own-video"  ref="your_video" :disabled="disabled" :zIndex="zIndex" :data="val" @change="onChangePosition" v-if="hasStream">
      <video class="conference-source__own-video__element" ref="video" autoplay muted></video>
    </BaseSource>

    <BaseSource v-if="value && value.conference && value.conference.connectedUsers" v-for="user in value.conference.connectedUsers" :key="user.id" class="conference-source__remote-video" :ref="'source_' + user.id"  :zIndex="zIndex" :data="user.position" @change="(e) => onChangeRemoteUserPosition(e, user)">
      <video class="conference-source__remote-video__element" :ref="'video_' + user.id" autoplay></video>
      <div class="conference-source__remote-video__user-info">{{userData[user.id]}}</div>
    </BaseSource>
  </div>

</template>
<style lang="scss">
  .conference-source {
    width: 100%;
    height: 100%;
    &__own-video {
      position: absolute;
      &__element {
        object-fit: cover;
        width: 100%;
        height: 100%;
      }
    }
    &__remote-video {
      position: absolute;
      &__element {
        object-fit: cover;
        width: 100%;
        height: 100%;
      }
    }
  }
</style>
<script>

    const gridTypes = [
        {name: 'GRID', positions: ({count}) => {
                if (count === 1) {
                    return [{x: 0, y: 0, w: 1, h: 1}]
                }
                if (count === 2) {
                    return [{x: 0, y: 0, w: .5, h: 1}, {x: .5, y: 0, w: .5, h: 1}]
                }
                if (count === 3) {
                    return [{x: 0, y: 0, w: .5, h: .5}, {x: .5, y: 0, w: .5, h: .5}, {x: 0, y: .5, w: 1, h: .5}]
                }
                const points = [2, 3, 4, 5]
                let itemsInRow = points.filter(item => item * item >= count)[0];
                let data = [];
                let rowsCount = Math.ceil(count / itemsInRow);
                let itemsInLastRow = count % itemsInRow;
                for (let i = 0; i < count - itemsInLastRow; i++) {
                    data.push({
                        w: 1 / itemsInRow,
                        h: 1 / rowsCount,
                        x: (1 / itemsInRow) * (i % itemsInRow),
                        y: (1 / rowsCount) * (Math.floor(i / itemsInRow) % rowsCount),
                    })
                }
                for (let i = count - itemsInLastRow; i < count; i++) {
                    data.push({
                        w: 1 / itemsInLastRow,
                        h: 1 / rowsCount,
                        x: (1 / itemsInLastRow) * (i % itemsInRow),
                        y: 1 - (1 / rowsCount),
                    })
                }
                return data;
            }},
        {name: 'GRID_FULLSCREEN', positions: ({count}) => {
                if (count === 1) {
                    return [{x: 0, y: 0, w: 1, h: 1}]
                }
                if (count <= 3) {
                    let data = [];
                    let height = 1 / count / 16 * 9;
                    for (let i = 0; i < count; i++) {
                        data.push({
                            w: (1 / count),
                            h: height,
                            x: (1 / count) * i,
                            y: .5 - (height / 2),
                        })
                    }
                }
                const points = [2, 3, 4, 5]
                let itemsInRow = points.filter(item => item * item >= count)[0];
                let data = [];
                let rowsCount = Math.ceil(count / itemsInRow);
                for (let i = 0; i < count; i++) {
                    data.push({
                        w: 1 / itemsInRow,
                        h: 1 / rowsCount,
                        x: (1 / itemsInRow) * (i % itemsInRow),
                        y: (1 / rowsCount) * (Math.floor(i / itemsInRow) % rowsCount),
                    })
                }
                return data;
            }},
        {name: 'BIG_AND_SMALL', maxCount: 2, positions: ({count}) => {
                let data = [];
                if (count > 0) {
                    data.push({
                        w: .8,
                        h: .8,
                        x: .1,
                        y: .1,
                    })
                }
                if (count > 1) {
                    data.push({
                        w: .25,
                        h: .25,
                        x: .1,
                        y: .7,
                    })
                }
            }},
        {name: 'DIALOG', maxCount: 2, positions: ({count}) => {
                let data = [];
                if (count > 0) {
                    data.push({
                        w: .4,
                        h: .8,
                        x: .1,
                        y: .1,
                    })
                }
                if (count > 1) {
                    data.push({
                        w: .4,
                        h: .8,
                        x: .6,
                        y: .1,
                    })
                }
            }},
        {name: 'SOUND_ONLY', positions: ({count}) => {
                return [];
            }}
    ];


    import RoomClient from '@/helpers/conference/RoomClient';

    import BaseSource from "./BaseSource";
    export default {
        props: ['value', 'object', 'zIndex', 'disabled'],
        beforeDestroy() {
            if (this.mirrorElement) {
                this.mirrorElement.remove();
            }
        },
        watch: {
            zIndex(newIndex) {
                if (this.mirrorElement) {
                    this.mirrorElement.style.zIndex = newIndex;
                }
            },
            disabled(isDisabled) {
                if (this.mirrorElement) {
                    this.mirrorElement.style.opacity = isDisabled ? 0 : 1;
                    if (isDisabled) {
                        this.mirrorElement.pause();
                    } else {
                        this.mirrorElement.play();
                    }
                    console.log('mirror element', this.mirrorElement);
                }
            },
            volume(newVolume) {
                if (newVolume > 1) {
                    this.volume = newVolume / 100;
                } else {
                    if (this.$refs.video) {
                        this.$refs.video.volume = newVolume;
                    }
                }
            },
            value: {
                handler(newVal) {
                    this.val = newVal;
                    this.setCamera();
                    this.volume = newVal.volume || 1;
                    if (this.mirrorElement) {
                        this.setMirrorElementPosition();
                    }
                },
                deep: true
            }
        },
        data() {
            return {
                val: this.value,
                volume: this.value ? this.value.volume / 100 : 1,
                devices: {
                    video: [],
                    audio: [],
                    loading: true,
                    error: null,
                },
                activeDevices: {
                    video: null,
                    audio: null
                },
                hasStream: false,
                stream: null,
                updateTimeout: null,
                mirrorContainer: null,
                mirrorElement: null,
                loadingCamera: false,
                peers: [],
                conference: null,
                userData: {},
            }
        },
        methods: {
            setLayout() {
                let count = this.value.conference.connectedUsers.length + 1;
                let positions = gridTypes[0].positions({count});
                this.value = {
                    ...this.value,
                    x: positions[0].x,
                    y: positions[0].y,
                    width: positions[0].w,
                    height: positions[0].h
                }
                this.value.conference.connectedUsers.forEach((user, index) => {
                    user.position = {
                        ...this.value,
                        x: positions[index + 1].x,
                        y: positions[index + 1].y,
                        width: positions[index + 1].w,
                        height: positions[index + 1].h
                    }
                })
                console.log('layout', this, this.value.conference.connectedUsers, positions);
            },
            onChangeRemoteUserPosition() {

            },
            setUserData(user) {
                this.$set(this.userData, user.id, user);
            },
            removePeer(id) {
                let index = this.value.conference.connectedUsers.map(user => user.id === id).indexOf(id);
                if (this.value.conference.connectedUsers[id]) {
                    if (this.value.conference.connectedUsers[id].fixed) {
                        this.value.conference.connectedUsers[id].disconnected = true;
                    } else {
                        this.value.conference.connectedUsers.splice(index, 1);
                    }
                }
            },
            setPeerStream({peerId, mediaStream}) {
                let video = this.$refs['video_' + peerId];
                if (video) {
                    if (video[0]) {
                        video = video[0];
                    }
                    video.srcObject = mediaStream;
                    video.play();
                }
            },
            addTestUser() {
                if (!this.value.conference.connectedUsers) {
                    this.$set(this.value.conference, 'connectedUsers', []);
                }
                this.$set(this.userData, "-1", {
                    username: "Test"
                })
                this.value.conference.connectedUsers.push({
                    id: "-1",
                    visible: true,
                    position: {
                        x: .5,
                        y: .5,
                        width: .5,
                        height: .5
                    }
                })
                this.$nextTick(() =>  {
                    this.setLayout();
                })
            },
            addPeer({peer, mediaStream}) {
                console.log('add peer', this);
                if (!this.value.conference.connectedUsers) {
                    this.$set(this.value.conference, 'connectedUsers', []);
                }
                this.value.conference.connectedUsers.push({
                    id: peer.name,
                    visible: true,
                    position: {
                        x: .5,
                        y: .5,
                        width: .5,
                        height: .5
                    }
                })
                this.$nextTick(() =>  {
                    let video = this.$refs['video_' + peer.name];
                    if (video) {
                        if (video[0]) {
                            video = video[0];
                        }
                        video.srcObject = mediaStream;
                        video.play();
                        this.setLayout();
                    }
                })
            },
            onChangePosition(position) {
                for (let key in position) {
                    this.value[key] = position[key];
                }
            },
            setMirrorElementPosition() {
                let mirrorElement = this.mirrorElement;
                if (!mirrorElement) {
                    return;
                }
                mirrorElement.style.width = this.value.width * 100 + '%';
                mirrorElement.style.height = this.value.height * 100 + '%';
                mirrorElement.style.left = this.value.x * 100 + '%';
                mirrorElement.style.top = this.value.y * 100 + '%';
            },
            bindElement(mirrorContainer) {
                this.mirrorContainer = mirrorContainer;
                let mirrorElement = document.createElement('video');
                this.mirrorElement = mirrorElement;
                mirrorElement.className = 'video_need_resume';
                mirrorElement.autoplay = true;
                mirrorElement.srcObject = this.stream;
                mirrorElement.style.position = 'absolute';
                mirrorElement.style.objectFit = 'cover';
                mirrorElement.style.zIndex = this.zIndex;
                this.setMirrorElementPosition();
                mirrorContainer.appendChild(mirrorElement);
            },
            getDevicesList() {
                this.$emit('devices-list', {audioDevicesList: this.devices.audio, videoDevicesList: this.devices.video});
            },
            setCamera(firstTime = false) {
                if (this.loadingCamera) {
                    return;
                }
               if (firstTime || this.value.videoDeviceId !== this.activeDevices.video || this.value.audioDeviceId !== this.activeDevices.audio) {
                    this.loadingCamera = true;
                    let params = {};
                    if (this.value.videoDeviceId) {
                        params.video = {
                            deviceId: {
                                exact: this.value.videoDeviceId
                            }
                        }
                    }
                    if (this.value.audioDeviceId) {
                        params.audio = {
                            deviceId: {
                                exact: this.value.audioDeviceId
                            }
                        }
                    }
                    if (!this.value.videoDeviceId || !this.value.audioDeviceId) {
                        params = {
                            video: true,
                            audio: true
                        }
                    }
                    navigator.mediaDevices.getUserMedia(params).then(stream => {
                        this.loadingCamera = false;
                        let videoTrack = stream.getVideoTracks()[0];
                        let audioTrack = stream.getAudioTracks()[0];
                        if (videoTrack) {

                            this.activeDevices.video = videoTrack.getSettings().deviceId;
                            if (!this.value.videoDeviceId) {
                                this.value.videoDeviceId = videoTrack.getSettings().deviceId;
                            }
                            let containerWidth = this.$refs.container.clientWidth;
                            let containerHeight = this.$refs.container.clientHeight;
                            if (!this.value.width) {
                                this.value.width = containerWidth / videoTrack.getSettings().width;
                            }
                            if (!this.value.height) {
                                this.value.height = containerHeight / videoTrack.getSettings().height;
                            }
                       }
                        if (audioTrack) {
                            this.activeDevices.audio = audioTrack.getSettings().deviceId;
                            if (!this.value.audioDeviceId) {
                                this.value.audioDeviceId = audioTrack.getSettings().deviceId;
                            }
                        }

                        this.hasStream = true;
                        this.stream = stream;
                        this.$nextTick(() => {
                            if (this.mirrorElement) {
                                this.mirrorElement.srcObject = stream;
                            }
                            this.$refs.video.srcObject = stream;
                            this.$refs.video.volume = this.volume;
                        })
                    }).catch(e => {
                        console.log('error', e);
                        this.loadingCamera = false;
                        console.log(e);
                    })
                }
            }
        },
        mounted() {
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
                                value: device.deviceId,
                                name: device.label || `Audio ${audioCount++}`
                            })
                        }
                        if (device.kind === 'videoinput') {
                            videoDevicesList.push({
                                value: device.deviceId,
                                name: device.label || `Video ${videoCount++}`
                            })
                        }
                        foundDevices[device.deviceId] = true;
                    }
                });
                this.devices.audio = audioDevicesList;
                this.devices.video = videoDevicesList;
                this.devices.loading = false;
                this.$emit('devices-list', {audioDevicesList, videoDevicesList});
                this.setCamera(true);
            }).catch(e => {
                this.devices.error = e.message;
                this.devices.loading = false;
            });
        },
        components: {
            BaseSource,
        }
    }
</script>
