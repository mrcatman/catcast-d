<template>
  <component @click="onThumbClick" ref="link" :is="channel.web_url ? 'a' : 'nuxt-link'"  :to="localUrl" >
    <slot></slot>
  </component>
</template>
<script lang="ts">
import { Vue, Prop, Component } from 'vue-property-decorator'
import Channel from '~/types/Channel'
@Component({})
export default class UserLink extends Vue {
  @Prop({required: true}) readonly channel!: Channel
  mounted() {
    if (this.channel.web_url) {
      let link = this.$refs.link as Element;
      link.setAttribute('href', this.channel.web_url);
    }
  }

  onThumbClick(e: any) {
    this.$router.push(this.localUrl);
    e.preventDefault();
  }

  get localUrl() {
    if (this.channel.domain) {
      return `/channels/${this.channel.id}`;
    } else {
      return `/${this.channel.url}`;
    }
  }
}
</script>
