<template>
  <div class="new-studio__conference-manager">
    Invited users: {{invitedUserIds}}
    <div class="new-studio__conference-manager__top">
      <c-preloader block  v-if="loading || state === 'STATE_CONNECTING'" />
      <div class="col">
        <c-button @click="init()" v-if="!conference">{{$t('studio.sources.video_conference.initialize')}}</c-button>
        <copy-tag v-else :text="conference.link"/>
      </div>
    </div>


    <div class="new-studio__conference-manager__users">
      <div class="new-studio__conference-manager__users__not-invited">
        <div class="new-studio__conference-manager__user" v-show="!userIsInConference(userId)" :key="userId" v-for="(peer, userId) in peers">
          <div class="new-studio__conference-manager__user__info">
            <div class="new-studio__conference-manager__user__ava" v-if="userData[userId]"  :style="{backgroundImage: 'url('+userData[userId].avatar+')'}"></div>
            <div class="new-studio__conference-manager__user__name">{{userData[userId] ? userData[userId].username : userId}}</div>
          </div>
          <div class="new-studio__conference-manager__user__buttons">
            <c-button @click="sendInviteRequest(userId)">{{$t('studio.sources.video_conference.invite')}}</c-button>
          </div>
        </div>
        <div class="new-studio__conference-manager__no-users" v-if="(!peers || Object.keys(peers).length === 0) && conference">
          {{$t('studio.sources.video_conference.waiting_for_users')}}
        </div>
      </div>
      <div class="new-studio__conference-manager__users__invited">
        <div class="new-studio__conference-manager__user" :key="user.id" v-for="(user, $index) in activeUsers">
          <div class="new-studio__conference-manager__user__pin" @click="toggleFixed(user)" :class="{'new-studio__conference-manager__user__pin--active': user.fixed}">
            <i class="fas fa-thumbtack"></i>
          </div>
          <div class="new-studio__conference-manager__user__info">
            <div class="new-studio__conference-manager__user__ava" v-if="userData[user.id]"  :style="{backgroundImage: 'url('+userData[user.id].avatar+')'}"></div>
            <div class="new-studio__conference-manager__user__name">{{userData[user.id] ? userData[user.id].username : user.id}}</div>
          </div>
          <div class="new-studio__conference-manager__user__buttons">
            <c-button color="red" @click="sendRejectRequest(user.id)">{{$t('global.delete')}}</c-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .new-studio__conference-manager {
    &__top {
      padding: .5em;
    }

    &__users {
      display: flex;
      &__not-invited, &__invited {
        width: calc(50% - 1em);
        background: rgba(255, 255, 255, 0.05);
        margin: .5em;
      }
    }

    &__no-users {
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    &__user {
      padding: .5em;
      transition: all .4s;
      margin: .5em;
      display: flex;
      width: calc(100% - 2em);
      justify-content: space-between;
      padding: .5em;
      &:hover {
        background: rgba(255, 255, 255, .1);
      }
      &--selected {
        background: rgba(255, 255, 255, .05);
      }
      &__info {
        display: flex;
        align-items: center;
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
      &__pin {
        padding: .5em .5em .5em 0;
        opacity: .25;
        cursor: pointer;

        &:hover {
          opacity: .75;
        }

        &--active {
          opacity: .85;
        }
      }
    }
  }
</style>
<script>
  import copyTag from '@/components/global/copyTag';
  import RoomClient from '@/helpers/conference/RoomClient';


  export default  {
      props: ['value', 'input', 'channel', 'overlay'],
      mounted() {
          this.init();
      },
      methods: {
          toggleFixed(user) {
              this.$set(user, 'fixed', !user.fixed);
          },
          sendRejectRequest(id) {
              this.$axios.post(`conferences/${this.conference.id}/users/reject`, {user_id: id}).then(res => {
                  if (res.data.status) {
                      this.overlay.object._componentInstance.removePeer(id)
                      this.roomClient.sendRejectRequest({id});
                      this.invitedUserIds.splice(this.invitedUserIds.indexOf(id), 1);
                      this.activeUsers.splice(this.activeUsers.map(user => user.id).indexOf(id), 1);
                  }  else {
                      this.$store.commit('NEW_ALERT', {status: 0, text: res.data.text});
                  }
              })
          },
          sendInviteRequest(id) {
              this.$axios.post(`conferences/${this.conference.id}/users/invite`, {user_id: id}).then(res => {
                  if (res.data.status) {
                      this.roomClient.sendRequest({id});
                      this.invitedUserIds.push(id);
                      this.activeUsers.push({
                          id,
                          fixed: false
                      })
                  } else {
                      this.$store.commit('NEW_ALERT', {status: 0, text: res.data.text});
                  }
              })
          },
          userIsInConference(userId) {
              return this.activeUsers.map(user => user.id).indexOf(userId) !== -1;
          },
          handleAddPeer({peer}) {
              this.$set(this.peers, peer.name, {
                  peer,
                  tracks: []
              });
              let activeUser = this.activeUsers.filter(user => user.id == peer.name)[0];
              if (!activeUser) {
                  let allowed = this.invitedUserIds.indexOf(peer.name) !== -1;
                  if (allowed) {
                      this.activeUsers.push({
                          id: peer.name,
                          fixed: false
                      })
                  }
              }
          },
          handleRemovePeer(id) {
              this.$delete(this.peers, id);
              this.overlay.object._componentInstance.removePeer(id)

              this.activeUsers.forEach((user, index) => {
                  if (user.id === id && !user.fixed) {
                      this.activeUsers.splice(index, 1);
                  }
              })
          },
          handleAddTrack({peer, track}) {
              console.log('Handle add track', peer, track);
              if (!this.peers[peer.name]) {
                  return;
              }
              this.peers[peer.name].tracks.push(track);
              let mediaStream = new MediaStream();
              this.peers[peer.name].tracks.forEach(track => {
                  mediaStream.addTrack(track);
              });
              if (!this.peers[peer.name].mediaActive) {
                 this.$set(this.peers[peer.name], 'mediaActive', true);
                 this.overlay.object._componentInstance.addPeer({
                     peer: this.peers[peer.name],
                     mediaStream,
                 })
             } else {
                  this.overlay.object._componentInstance.setPeerStream({
                      peerId: peer.name,
                      mediaStream,
                  })
              }

          },
          handleUserData(user) {
              this.$set(this.userData, user.id, user);
              this.overlay.object._componentInstance.setUserData(user)
          },
          handleJoinSuccess() {
              this.state = "STATE_STARTED";
              this.roomClient.startSendingMedia();
          },
          handleJoinError(e) {
              this.state = "STATE_NOT_STARTED";
              this.$store.commit('NEW_ALERT', {no_translate: true, status: 0, text: e});
          },
          async connect() {
              if (!this.overlay) {
                  return;
              }
              this.state = "STATE_CONNECTING";
              let roomId = this.conference.uuid;
              let peerName = "" + this.$store.state.userData.id;
              let token = this.$store.state.token;

              let server = (await this.$api.get('conferences/server')).data.server;
              this.roomClient = new RoomClient({server, roomId, peerName, token});
              this.roomClient.setPredefinedMediaStream(this.overlay.object._componentInstance.stream);
              this.roomClient.on('add-peer', this.handleAddPeer);
              this.roomClient.on('remove-peer', this.handleRemovePeer);
              this.roomClient.on('add-track', this.handleAddTrack);
              this.roomClient.on('user-data', this.handleUserData);
              this.roomClient.on('join-success', this.handleJoinSuccess);
              this.roomClient.on('join-error', this.handleJoinError);
              this.roomClient.connect();
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
                      this.invitedUserIds = data.data.conference.settings && data.data.conference.settings.allowed_users ? data.data.conference.settings.allowed_users : [];
                  }

                  this.connect();
              } else {
                  this.$store.commit("NEW_ALERT", data);
              }
          }
      },
      watch: {
          val(newVal) {
              this.$emit('input', newVal);
          }
      },
      data() {
          return {
              loading: false,
              state: "STATE_NOT_STARTED",
              conference: null,
              val: this.value,
              peers: {},
              userData: {},
              activeUsers: [],
              invitedUserIds: [],
          }
      },
      components: {
          copyTag
      }
  }
</script>
