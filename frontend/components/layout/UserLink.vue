<template>
  <component @click="onThumbClick" ref="link" :is="user.web_url ? 'a' : 'nuxt-link'"  :to="localUrl" >
    <slot></slot>
  </component>
</template>
<script lang="ts">
import Vue, {PropType} from 'vue';
import User from '~/types/User'
export default Vue.extend({
  name: 'UserLink',
  mounted() {
    if (this.user.web_url) {
      this.$refs.link.setAttribute('href', this.user.web_url);
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
       if (this.user.domain) {
        return `/users/${this.user.id}`;
      } else {
        return `/users/${this.user.login}`;
      }
    }
  },
  props: {
    user: {
      type: Object as PropType<User>,
      required: true
    }
  }
})
</script>
