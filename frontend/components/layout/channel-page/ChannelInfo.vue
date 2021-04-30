<template>
  <div class="channel-info" v-if="channelData">
    <StreamInfoModal @saved="onStreamInfoSaved" :channel="channelData" v-model="streamInfoModalVisible" />

    <div class="channel-info__top">
      <PictureBlock class="channel-info__logo" :data="channel.logo" defaultFromConfig="channel_default_logo"></PictureBlock>
      <div class="channel-info__texts">
        <div class="channel-info__texts__top">
          <h1 class="channel-info__stream-name" v-if="channelData.is_online && channelData.current_stream">{{channelData.current_stream.name}}</h1>
          <h2 class="channel-info__name">{{channelData.name}}<m-button v-if="canEditStreamInfo" @click="streamInfoModalVisible = true" class="channel-info__edit-stream" flat icon="edit"></m-button></h2>
          <div class="channel-info__owner" v-if="channelData.owner">{{$t('channels.created_by')}}
            <UserLink class="channel-info__owner__link" :user="channelData.owner">
              <div  class="channel-info__owner__avatar" v-if="channelData.owner.avatar" :style="{backgroundImage: `url(${channelData.owner.avatar.full_url})`}"></div>
              {{channelData.owner.activitypub_handle}}
            </UserLink>
          </div>
          <a v-if="channelData.domain" class="channel-info__remote-url" target="_blank" :href="`https://${channelData.domain}/${channelData.url}`">{{channelData.url}}@{{channelData.domain}}</a>
        </div>
        <SubscribeBlock :channel="channel" />
      </div>
    </div>
    <div class="channel-info__description-container">
      <div class="channel-info__stream-description" v-if="channelData.is_online && channelData.current_stream">{{channelData.current_stream.description}}</div>
      <div class="channel-info__description">{{channelData.description}}</div>
    </div>

  </div>
</template>
<style lang="scss">
  .channel-info {
    margin: 1em 0 0;
    position: relative;
    .subscribe-block {
      position: absolute;
      top: 0;
      right: 0;
      @media screen and (max-width: 768px) {
        margin: 1em 0 0;
        position: unset;
      }
    }
    &__top {
      display: flex;
      align-items: flex-start;
    }
    &__logo {
      background-color: rgba(0, 0, 0, .5);
      width: 5em;
      height: 5em;
      background-size: contain;
      background-position: center;
      background-repeat: no-repeat;
    }

    &__name {
      display: inline-flex;
      align-items: center;
      font-size: 1.325em;
      margin: 0 0 .5em;
      font-weight: 400;
    }
    &__remote-url {
      font-size: .9375em;
    }
    &__stream-name {
      font-size: 1.75em;
      margin: 0;
    }
    &__texts {
      margin: 0 0 0 1em;
    }

    &__description-container {
      margin: .5em 0 0;
      line-height: 1.75;
    }
    &__stream-description {
      font-size: 1.25em;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    &__edit-stream {
      font-size: .625em;
      margin: 0 0 0 1em;
      background: rgba(255, 255, 255, 0.025);
      .button__content {
        padding: 1em;
      }
    }
    &__owner {
      display: flex;
      align-items: center;
      font-size: .9375em;

      &__link {
        margin: 0 0 0 .5em;
        display: inline-flex;
        align-items: center;
        text-decoration: none;

        &__avatar {
          width: 1.75em;
          height: 1.75em;
          background-size: contain;
          background-position: center;
          background-repeat: no-repeat;
          margin: 0 .25em 0 0;
        }
      }
    }
  }
</style>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import Channel from '~/types/Channel'
import { Prop } from '~/node_modules/vue-property-decorator'
import SubscribeBlock from '~/components/layout/channel-page/SubscribeBlock.vue'
import { UserChannelPermissions } from '~/helpers/permissions'
import StreamInfoModal from '~/components/layout/channel-page/StreamInfoModal.vue'
import UserLink from '~/components/layout/UserLink.vue'
import PictureBlock from '~/components/layout/PictureBlock.vue'

  @Component({
    components: { PictureBlock, StreamInfoModal, SubscribeBlock, UserLink },
  })
  export default class ChannelInfoBlock extends Vue {
    streamInfoModalVisible: boolean = false;
    channelData: Channel | null = null;

    mounted() {
      this.channelData = this.channel;
    }

    onStreamInfoSaved(streamInfo: any) {
      if (this.channelData && this.channelData.current_stream ) {
        this.channelData.current_stream = { ...this.channelData.current_stream, ...streamInfo };
      }
    }

    get canEditStreamInfo() {
      return this.permissions.includes(UserChannelPermissions.EDIT_STREAM_INFO);
    }

    @Prop({default: null}) readonly channel!: Channel
    @Prop({required: true}) readonly permissions!: Array<UserChannelPermissions>
  }
</script>
