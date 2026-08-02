<template>
  <div class="chat-page">
    <chat :channel="channel"/>
  </div>
</template>
<style lang="scss">
.chat-page {
  height: 100%;
}
</style>
<script>
  import Chat from '@/components/Chat';
  export default {
    layout: 'empty',
    head () {
      return {
        title: this.$t('chat.heading'),
      }
    },
    components: {
      Chat
    },
    async asyncData({app,params}) {
      const channel = await app.$api.get(`/channels/${params.id}`);
      return {
        channel,
      }
    },
  }
</script>
