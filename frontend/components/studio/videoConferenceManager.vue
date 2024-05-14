<template>
  <div class="video-conference-manager">
    <div class="row">
      <c-preloader block  v-if="loading" />
      <div class="col">
        <c-button @click="init()" v-if="!conference">{{$t('studio.sources.video_conference.initialize')}}</c-button>
        <copy-tag v-else :text="conference.link"/>
      </div>
     <div class="col">

      </div>
    </div>
    <div class="video-conference-manager__layout-types">
      <a @click="setLayoutType('fit')" class="video-conference-manager__layout-type" :class="{'video-conference-manager__layout-type--active': layoutType === 'fit'}">{{$t('studio.sources.video_conference.layout_types.fit')}}</a>
      <a @click="setLayoutType('fill_screen')" class="video-conference-manager__layout-type" :class="{'video-conference-manager__layout-type--active': layoutType === 'fill_screen'}">{{$t('studio.sources.video_conference.layout_types.fill_screen')}}</a>
      <a @click="setLayoutType('big_remote')" class="video-conference-manager__layout-type" :class="{'video-conference-manager__layout-type--active': layoutType === 'big_remote'}">{{$t('studio.sources.video_conference.layout_types.big_remote')}}</a>
      <a @click="setLayoutType('big_local')" class="video-conference-manager__layout-type" :class="{'video-conference-manager__layout-type--active': layoutType === 'big_local'}">{{$t('studio.sources.video_conference.layout_types.big_local')}}</a>
    </div>
    <div class="video-conference-manager__users">
      <div class="video-conference-manager__user" v-if="Object.keys(peers).length > 0">
        <div class="video-conference-manager__user__info">
            <div class="video-conference-manager__user__name">{{$t('studio.sources.video_conference.you')}}</div>
        </div>
        <div class="video-conference-manager__user__buttons">
          <c-button color="green" @click="setAsMain(null)" v-if="mainUserId != null"> {{$t('studio.sources.video_conference.focus_1')}}</c-button>
        </div>
      </div>
      <div class="video-conference-manager__user" :class="{'video-conference-manager__user--selected' : userIsInConference(userId)}" :key="userId" v-for="(peer, userId) in peers">
        <div class="video-conference-manager__user__info">
          <div class="video-conference-manager__user__ava" v-if="userData[userId]"  :style="{backgroundImage: 'url('+userData[userId].avatar+')'}"></div>
          <div class="video-conference-manager__user__name">{{userData[userId] ? userData[userId].username : userId}}</div>
        </div>
        <video style="display: none" :ref="'video_'+userId"></video>
        <div class="video-conference-manager__user__buttons">
          <c-button @click="sendRequest(userId)" v-if="!userIsInConference(userId)">{{$t('studio.sources.video_conference.invite')}}</c-button>
          <c-button color="green" @click="setAsMain(userId)"  v-if="userIsInConference(userId) && mainUserId != userId && Object.keys(peers).length > 1"> {{$t('studio.sources.video_conference.focus_1')}}</c-button>
          <c-button color="green" @click="setAsSecond(userId)"  v-if="userIsInConference(userId) && secondUserId != userId && Object.keys(peers).length > 1"> {{$t('studio.sources.video_conference.focus_2')}}</c-button>
          <c-button v-if="userIsInConference(userId)" color="red" @click="sendRejectRequest(userId)">{{$t('global.delete')}}</c-button>
        </div>
      </div>
      <div class="video-conference-manager__no-users" v-if="(!peers || Object.keys(peers).length === 0) && conference">
        {{$t('studio.sources.video_conference.waiting_for_users')}}
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .video-conference-manager {
    padding: .5em;
    position: relative;
    &__users {
      display: flex;
      flex-wrap: wrap;
      margin: .5em 0 0;
    }
    &__user {
      padding: .5em;
      transition: all .4s;
      width: calc(50% - 2em);
      margin: .5em;
      &:hover {
        background: rgba(255, 255, 255, .1);
      }
      &--selected {
        background: rgba(255, 255, 255, .05);
      }
      &__info {
        display: flex;
        align-items: center;
        margin: 0 0 .5em;
      }

      &__avatar {
        width: 2em;
        height: 2em;
        background-size: contain !important;
        background-repeat: no-repeat;
      }

      &__name {
        margin: 0 0 0 .5em;
        font-weight: 500;
      }
    }
    &__layout-types {
      margin: 1em 0 0;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      font-size: .875em;
    }

    &__layout-type {
      display: inline-block;
      background: rgba(255, 255, 255, 0.1);
      padding: .5em 1em;
      border-radius: var(--border-radius);
      cursor: pointer;
      transition: all .4s;
      width: calc(50% - 2em);
      text-align: center;
      &:hover {
        background: rgba(255, 255, 255, 0.35);
      }

      &--active {
        background: var(--active-color)!important;
      }
    }

  }
</style>
<script>
  import copyTag from '@/components/global/copyTag';
  import RoomClient from '@/helpers/conference/RoomClient';

  export default {
      components: {
          copyTag
      },
      async mounted() {
        if (this.object.data && this.object.data.data && this.object.data.data.conference_id) {
          this.loading = true;
          let data = (await this.$api.get(`conferences/${this.object.data.data.conference_id}`));
          if (data.status) {
            this.conference = data.data.conference;
            this.init();
          }
          this.loading = false;
        }
        if (this.object.videos.length > 0) {
          this.setLayout();
        }
     },
     methods: {
        setAsSecond(userId) {
           this.secondUserId = userId;
           this.setLayout();
        },
        setAsMain(userId) {
           this.mainUserId = userId;
           this.setLayout();
        },
        userIsInConference(userId) {
            return this.peers[userId] && this.peers[userId].mediaActive;
        },
        setLayout() {
          //if (this.object.data.ownCamera) {
          let firstUserIndex = this.mainUserId ? this.peers[this.mainUserId].videoIndex : 0;
          let secondUserIndex = this.secondUserId ? this.peers[this.secondUserId].videoIndex : 1;

          let localVideo = this.object.videos[0];
          let remoteVideo = this.object.videos[1];
          let sizeX = this.object.data.settings.sizeX;
          let sizeY = this.object.data.settings.sizeY;
          let data = {};
          if (this.layoutType === 'fit') {
              if (localVideo) {
                  let localSizeY = (sizeX / 2) * (localVideo.videoHeight / localVideo.videoWidth);
                  let localVideoSettings = {
                      x: 0,
                      y: (sizeY - localSizeY) / 2,
                      startX: 0,
                      startY: 0,
                      endX: localVideo.videoWidth,
                      endY: localVideo.videoHeight,
                      sizeX: sizeX / 2,
                      sizeY: localSizeY
                  };
                  data[firstUserIndex] = localVideoSettings;
              }
              if (remoteVideo) {
                  let remoteSizeY = (sizeX / 2) * (remoteVideo.videoHeight / remoteVideo.videoWidth);
                  let remoteVideoSettings = {
                    x: sizeX / 2,
                    y: (sizeY - remoteSizeY) / 2,
                    startX: 0,
                    startY: 0,
                    endX: remoteVideo.videoWidth,
                    endY: remoteVideo.videoHeight,
                    sizeX: sizeX / 2,
                    sizeY: remoteSizeY
                  };
                  data[secondUserIndex] = remoteVideoSettings;
              }
          }
          if (this.layoutType === 'fill_screen') {
              if (localVideo) {
                  let fullLocalSizeX = sizeY / (localVideo.videoHeight / localVideo.videoWidth);
                  let localOffsetX = sizeX - fullLocalSizeX;
                  let localVideoSettings = {
                      x: 0,
                      y: 0,
                      startX: localOffsetX,
                      startY: 0,
                      endX: localVideo.videoWidth - localOffsetX / (sizeY / localVideo.videoHeight),
                      endY: localVideo.videoHeight,
                      sizeX: sizeX / 2,
                      sizeY: sizeY
                  };
                  data[firstUserIndex] = localVideoSettings;
              }
              if (remoteVideo) {
                  let fullRemoteSizeX = sizeY / (remoteVideo.videoHeight / remoteVideo.videoWidth);
                  let remoteOffsetX = sizeX - fullRemoteSizeX;
                  let remoteVideoSettings = {
                      x: sizeX / 2,
                      y: 0,
                      startX: remoteOffsetX,
                      startY: 0,
                      endX: remoteVideo.videoWidth - remoteOffsetX / (sizeY / remoteVideo.videoHeight),
                      endY: remoteVideo.videoHeight,
                      sizeX: sizeX / 2,
                      sizeY: sizeY
                  };
                  data[secondUserIndex] = remoteVideoSettings;
              }
          }
          if (this.layoutType === 'big_remote' || this.layoutType === 'big_local') {
            let bigVideo = this.layoutType === 'big_remote' ? remoteVideo : localVideo;
            let smallVideo = this.layoutType === 'big_remote' ? localVideo : remoteVideo;
            if (bigVideo) {
                let bigVideoSizeX = bigVideo.videoWidth * sizeY / bigVideo.videoHeight;
                let bigVideoSettings = {
                    x: (sizeX - bigVideoSizeX) / 2,
                    y: 0,
                    startX: 0,
                    startY: 0,
                    endX: bigVideo.videoWidth,
                    endY: bigVideo.videoHeight,
                    sizeX: bigVideoSizeX,
                    sizeY: sizeY
                };
                data[this.layoutType === 'big_local' ? firstUserIndex : secondUserIndex] = bigVideoSettings;
            }
            if (smallVideo) {
                let smallVideoSizeX = sizeX / 4;
                let smallVideoSizeY = smallVideoSizeX * (smallVideo.videoHeight / smallVideo.videoWidth);
                let smallVideoSettings = {
                    x: 10,
                    y: sizeY - smallVideoSizeY - 10,
                    startX: 0,
                    startY: 0,
                    endX: smallVideo.videoWidth,
                    endY: smallVideo.videoHeight,
                    sizeX: smallVideoSizeX,
                    sizeY: smallVideoSizeY
                };
                data[this.layoutType === 'big_local' ? secondUserIndex : firstUserIndex] = smallVideoSettings;
                //if (this.layoutType === 'big_local') {
                //} else {
                //  data.unshift(smallVideoSettings);
                //}
            }
          }
          if (this.object.videos.length > 2) {
              let additionalVideoSizeX = this.object.videos.length > 6 ? sizeX / (this.object.videos.length - 2 ) : sizeX / 4;
              let localIndex = 0;
              this.object.videos.forEach((video, index) => {
                  console.log('Placing video', index, secondUserIndex, additionalVideoSizeX);
                  if (index !== firstUserIndex && index !== secondUserIndex)  {
                      let additionalVideoSizeY = additionalVideoSizeX * (video.videoHeight / video.videoWidth);
                      data[index] = {
                          x: additionalVideoSizeX * localIndex,
                          y: 0,
                          startX: 0,
                          startY: 0,
                          endX: video.videoWidth,
                          endY: video.videoHeight,
                          sizeX: additionalVideoSizeX,
                          sizeY: additionalVideoSizeY
                      }
                      localIndex++;
                  }
              })
          }
          console.log(data, this.object);
          this.object.videoSizes = Object.values(data);
          this.object.ownVideoSizes = true;
        },
        sendRejectRequest(id) {
            if (this.peers[id] && this.peers[id].videoIndex) {
                this.peers[id].mediaActive = false;
                let video = this.object.videos[this.peers[id].videoIndex];
                if (video) {
                    video.pause();
                    this.object.videos.splice(this.peers[id].videoIndex, 1);
                }
            }
            if (this.mainUserId == id) {
                this.mainUserId = null;
            }
            if (this.secondUserId == id) {
                this.secondUserId = null;
            }
            this.setLayout();
            this.roomClient.sendRejectRequest({id});

        },
        sendRequest(id) {
            this.roomClient.sendRequest({id});
        },
        async connect() {
            let roomId = this.conference.uuid;
            let peerName = "" + this.$store.state.userData.id;
            let token = this.$store.state.token;

            let server = (await this.$api.get('conferences/server')).data.server;
            this.roomClient = new RoomClient({server, roomId, peerName, token});
            this.roomClient.setPredefinedMediaStream(this.object.mediaStream);
            this.roomClient.on('add-peer', this.handleAddPeer);
            this.roomClient.on('remove-peer', this.handleRemovePeer);
            this.roomClient.on('add-track', this.handleAddTrack);
            this.roomClient.on('user-data', this.handleUserData);
            this.roomClient.on('join-success', this.handleJoinSuccess);
            this.roomClient.on('join-error', this.handleJoinError);
            this.roomClient.on('effective-profile-change', () => {
                this.$nextTick(() => {
                    this.setLayout();
                })
                setTimeout(() => {
                    this.setLayout();
                }, 250)
            });
            this.roomClient.connect();
        },
        handleAddPeer({peer}) {
            this.$set(this.peers, peer.name, {
                peer,
                tracks: []
            });
        },
        handleRemovePeer(id) {
            this.peers[id].mediaActive = false;
            let video = this.object.videos[this.peers[id].videoIndex];
            if (video) {
                video.pause();
                this.object.videos.splice(this.peers[id].videoIndex, 1);
            }
            if (this.mainUserId == id) {
                this.mainUserId = null;
            }
            if (this.secondUserId == id) {
                this.secondUserId = null;
            }
            this.$delete(this.peers, id);
            this.setLayout();
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
            this.peers[peer.name].mediaStream = mediaStream;

            this.$nextTick(() => {
                let videos = this.$refs['video_'+peer.name];
                if (videos && videos[0]) {
                    let video = videos[0];
                    video.srcObject = mediaStream;
                    console.log(mediaStream);
                    video.pause();

                    setTimeout( ()  => {
                        video.play();
                    }, 250);
                    if (!this.peers[peer.name].mediaActive) {
                        this.$set(this.peers[peer.name], 'mediaActive', true);
                        video.onloadedmetadata = (e) => {
                            console.log('loaded remote metadata');
                            this.object.videoActive = true;
                            this.object.videos.push(video);
                            this.peers[peer.name].videoIndex = this.object.videos.length - 1;
                            this.object.audioActive = true;
                            this.$nextTick(() => {
                                this.setLayout();
                            })
                        }
                        video.onloadeddata = (e) => {
                            console.log('loaded remote data');
                            this.object.videoActive = true;
                            if (!this.audioConnected) {
                                let src = this.object.audioCtx.createMediaElementSource(video);
                                this.object.audios.push(src);
                                this.object.onLoadedAudio();
                            }
                        }
                    }
                }
            })
        },
        handleUserData(user) {
            this.$set(this.userData, user.id, user);
        },
        handleJoinSuccess() {
            this.roomClient.startSendingMedia();
        },
        handleJoinError(e) {
            this.$store.commit('NEW_ALERT', {no_translate: true, status: 0, text: e});
        },
        setLayoutType(type) {
            this.layoutType = type;
            this.setLayout();
        },
        async init() {
            let hasAlreadyLoaded = !!this.conference;
            let data = null;
            if (!hasAlreadyLoaded) {
                data = (await this.$axios.post(`channels/${this.channel.id}/conference`)).data;
            }
            if ((data && data.status) || hasAlreadyLoaded) {
                if (!hasAlreadyLoaded) {
                this.conference = data.data.conference;
                this.object.setSubData({
                  conference_id: data.data.conference.uuid
                });
              }
              this.connect();
            } else {
              this.$store.commit("NEW_ALERT", data);
            }
        }
    },
    data() {
        return {
            userData: {},
            roomClient: null,
            audioConnected: null,
            layoutType: 'fill_screen',
            peers: {},
            conference: null,
            mainUserId: null,
            secondUserId: null,
            loading: false,
        }
    },
    props: {
        object: {
            type: Object,
            required: true
        },
        channel: {
            type: Object,
            required: true
        }
    }
  }
</script>
