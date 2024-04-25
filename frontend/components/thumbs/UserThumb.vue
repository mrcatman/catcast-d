<template>
  <nuxt-link class="user-thumb" :to="link">
    <div class="user-thumb__avatar" :style="{backgroundImage: `url(${data.avatar})`}"></div>
    <span class="user-thumb__username">{{data.username}}</span>
  </nuxt-link>
</template>
<style lang="scss">
  .user-thumb {
    text-decoration: none;
    font-size: .875em;
    text-align: center;
    transition: opacity .2s;
    &:hover {
      opacity: .75;
    }
    &__username {
      overflow: hidden;
      text-overflow: ellipsis;
    }
    &__avatar {
      width: calc(100% - 1em);
      padding-top: calc(100% - 1em);
      background-size: contain;
      background-position: center;
      background-repeat: no-repeat;
      margin: 0 .5em .25em;
    }
  }
</style>
<script>
  export default {

    computed: {
      link() {
        return `/users/${this.data.id}`;
      },
    },
    props: {
      data: {
        type: Object,
        required: true
      }
    }
  }
</script>
