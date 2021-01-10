<template>
  <div class="channel-info">
    <div class="channel-info__top">
      <div class="channel-info__logo" v-if="channel.logo" :style="{backgroundImage: `url(${channel.logo.full_url})`}"></div>
      <div class="channel-info__texts">
        <div class="channel-info__texts__top">
          <h2 class="channel-info__name">{{channel.name}}</h2>
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
    &__top {
      display: flex;
      align-items: center;
    }
    &__logo {
      width: 5em;
      height: 5em;
      background-size: contain;
      background-position: center;
      background-repeat: no-repeat;
    }

    &__name {
      display: inline-block;
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
  }
</style>
<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'
  import Channel from '~/types/Channel'
  import { Prop } from '~/node_modules/vue-property-decorator'
  import SubscribeBlock from '~/components/layout/channel-page/SubscribeBlock.vue'

  @Component({
    components: { SubscribeBlock },
  })
  export default class ChannelHeaderBlock extends Vue {
    @Prop({default: null}) readonly channel!: Channel

  }
</script>
