<template>
  <component @click="onThumbClick" ref="link" :is="channel.web_url ? 'a' : 'nuxt-link'"  :to="localUrl" >
    <slot></slot>
  </component>
</template>
<script lang="ts">
import Vue, {PropType} from 'vue';
import Channel from '@/types/Channel'
export default Vue.extend({
  name: 'ChannelLink',
  mounted() {
    if (this.channel.web_url) {
      this.$refs.link.setAttribute('href', this.channel.web_url);
    }
  },
  methods: {
    onThumbClick(e: any) {
      this.$router.push(this.localUrl);
      e.preventDefault();
    }
  },
  computed: {
    localUrl() {
       if (this.channel.domain) {
        return `/channels/${this.channel.id}`;
      } else {
        return `/${this.channel.url}`;
      }
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
