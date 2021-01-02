<template>
  <router-link :to="`/${channel.url}`" class="thumb thumb--channel">
    <div class="thumb__picture" :style="{backgroundImage: `url(${channel.is_online ? channel.screenshot : (channel.logo ? channel.logo.full_url : null)}`}"></div>
    <div class="thumb__inner">
      <div class="thumb__logo" v-if="channel.logo" :style="{backgroundImage: `url(${channel.logo.full_url}`}"></div>
      <div class="thumb__texts">
        <div class="thumb__main-title">{{channel.stream_name}}</div>
        <div class="thumb__title">{{channel.name}}</div>
      </div>
    </div>
  </router-link>
</template>
<script lang="ts">
  import Vue, {PropType} from 'vue';
  import Channel from '@/types/Channel'
  export default Vue.extend({
    name: 'Channel',
    props: {
      channel: {
        type: Object as PropType<Channel>,
        required: true
      }
    }
  })
</script>
