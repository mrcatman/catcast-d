<template>
  <component :is="!disableLinks ? 'nuxt-link' : 'div'" :to="!disableLinks ? {path: link, params: {channel}} : null" class="channel-logo-and-name">
    <div class="channel-logo-and-name__logo" v-if="channel.logo" :style="{backgroundImage: `url(${channel.logo})`}"></div>
    <span class="channel-logo-and-name__name">{{channel.name}}</span>
  </component>
</template>
<style lang="scss" scoped>
.channel-logo-and-name {
  text-decoration: none;
  display: flex;
  align-items: center;
  &__logo {
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    width: 1.325em;
    height: 1.325em;
    margin-right: .5em;
  }
  &__name {
    font-weight: 600;
  }
}
</style>
<script>
export default {
  computed: {
    link() {
      return `/${this.channel.shortname}`;
    },
  },
  props: {
    channel: {
      type: Object,
      required: true
    },
    disableLinks: {
      type: Boolean,
      required: false
    }
  }
}
</script>
