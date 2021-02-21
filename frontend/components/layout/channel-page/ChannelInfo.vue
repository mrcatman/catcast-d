<template>
  <div class="channel-info">
    <StreamInfoModal :channel="channel" v-model="streamInfoModalVisible" />

    <div class="channel-info__top">
      <div class="channel-info__logo" v-if="channel.logo" :style="{backgroundImage: `url(${channel.logo.full_url})`}"></div>
      <div class="channel-info__texts">
        <div class="channel-info__texts__top">
          <h2 class="channel-info__name">{{channel.name}}<m-button v-if="canEditStreamInfo" @click="streamInfoModalVisible = true" class="channel-info__edit-stream" flat icon="edit"></m-button></h2>
          <a v-if="channel.domain" class="channel-info__remote-url" target="_blank" :href="`https://${channel.domain}/${channel.url}`">{{channel.url}}@{{channel.domain}}</a>
        </div>
        <SubscribeBlock :channel="channel" />
      </div>
    </div>
    <div class="channel-info__description">{{channel.description}}</div>
  </div>
</template>
<style lang="scss">
  .channel-info {
    margin: 1em 0 0;
    &__top {
      display: flex;
      align-items: center;
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
    }
    &__remote-url {
      font-size: .9375em;
    }

    &__texts {
      margin: 0 0 0 1em;
    }

    &__description {
      margin: 1em 0 0;
      line-height: 1.75;
    }
    &__edit-stream {
      font-size: .625em;
      margin: 0 0 0 1em;
      background: rgba(255, 255, 255, 0.025);
      .button__content {
        padding: 1em;
      }
    }
  }
</style>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import Channel from '~/types/Channel'
import { Prop } from '~/node_modules/vue-property-decorator'
import SubscribeBlock from '~/components/layout/channel-page/SubscribeBlock.vue'
import { ChannelPermissions } from '~/helpers/channelPermissions'
import StreamInfoModal from '~/components/layout/channel-page/StreamInfoModal.vue'

@Component({
    components: { StreamInfoModal, SubscribeBlock },
  })
  export default class ChannelInfoBlock extends Vue {
    streamInfoModalVisible: boolean = false;

    get canEditStreamInfo() {
      return this.permissions.includes(ChannelPermissions.EDIT_STREAM_INFO);
    }

    @Prop({default: null}) readonly channel!: Channel
    @Prop({required: true}) readonly permissions!: Array<ChannelPermissions>
  }
</script>
