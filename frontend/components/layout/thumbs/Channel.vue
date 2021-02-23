<template>
  <component :is="channel.web_url ? 'a' : 'router-link'" :href="channel.web_url" :to="localUrl" @click="onThumbClick" class="thumb thumb--channel">
    <div class="thumb__picture" :style="{backgroundImage: `url(${channel.is_online && channel.current_stream ? channel.current_stream.cover_url : (channel.logo ? channel.logo.full_url : null)}`}"></div>
    <div class="thumb__inner">
      <span class="thumb__badge" v-if="channel.is_online">LIVE</span>
      <div class="thumb__logo" v-if="channel.logo" :style="{backgroundImage: `url(${channel.logo.full_url}`}"></div>
      <div class="thumb__texts">
        <div class="thumb__main-title" v-if="channel.is_online">{{channel.current_stream.name}}</div>
        <div class="thumb__title">{{channel.name}}</div>
      </div>
    </div>
  </component>
</template>
<script lang="ts">
  import Vue, {PropType} from 'vue';
  import { Route } from "vue-router"
  import Channel from '@/types/Channel'
  export default Vue.extend({
    name: 'Channel',
    computed: {
      localUrl() {
        let channel = this.channel as Channel;
        if (channel.domain) {
          return `/channels/${channel.id}`;
        } else {
          return `/${channel.url}`;
        }
      }
    },
    methods: {
      onThumbClick(e: any) {
        this.$router.push(this.localUrl);
        e.preventDefault();
      }
    },
    props: {
      channel: {
        type: Object as PropType<Channel>,
        required: true
      }
    }
  })
</script>
