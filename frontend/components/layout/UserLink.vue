<template>
  <component @click="onThumbClick" ref="link" :is="user.web_url ? 'a' : 'nuxt-link'"  :to="localUrl" >
    <slot></slot>
  </component>
</template>
<script lang="ts">
import { Vue, Prop, Component } from 'vue-property-decorator'
import User from '~/types/User'
@Component({})
export default class UserLink extends Vue {
  @Prop({required: true}) readonly user!: User

  mounted() {
    if (this.user.web_url) {
      let link = this.$refs.link as Element;
      link.setAttribute('href', this.user.web_url);
    }
  }

  onThumbClick(e: any) {
    this.$router.push(this.localUrl);
    e.preventDefault();
  }

  get localUrl() {
    if (this.user.domain) {
      return `/users/${this.user.id}`;
    } else {
      return `/users/${this.user.login}`;
    }
  }
}
</script>
