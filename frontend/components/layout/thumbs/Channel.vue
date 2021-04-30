<template>
  <ChannelLink :channel="channel" class="thumb thumb--channel">
    <div class="thumb__picture" v-if="channel.is_online && channel.current_stream"  :style="{backgroundImage: `url(${channel.current_stream.cover_url})`}"></div>
    <PictureBlock class="thumb__picture thumb--channel__logo" v-else :data="channel.logo" defaultFromConfig="channel_default_logo" />
    <div class="thumb__inner">
      <span class="thumb__badge" v-if="channel.is_online">LIVE</span>
      <PictureBlock class="thumb__logo" :data="channel.logo" defaultFromConfig="channel_default_logo"></PictureBlock>
      <div class="thumb__texts">
        <div class="thumb__main-title" v-if="channel.is_online">{{channel.current_stream.name}}</div>
        <div class="thumb__title">{{channel.name}}</div>
      </div>
    </div>
  </ChannelLink>
</template>
<style lang="scss">
.thumb--channel {
  &__logo {
    background-size: contain;
    background-repeat: no-repeat;
  }
}
</style>
<script lang="ts">
  import Vue, {PropType} from 'vue';
  import { Route } from "vue-router"
  import Channel from '@/types/Channel'
  import ChannelLink from '~/components/layout/ChannelLink.vue';
  import PictureBlock from '~/components/layout/PictureBlock.vue'
  export default Vue.extend({
    components: {
      PictureBlock,
      ChannelLink
    },
    name: 'Channel',

    props: {
      channel: {
        type: Object as PropType<Channel>,
        required: true
      }
    }
  })
</script>
