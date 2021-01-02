<template>
  <div>
    {{subscribersCount}} {{isSubscribed}}
  </div>
</template>
<style lang="scss">

</style>
<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'
  import Channel from '~/types/Channel'
  import {  ChannelGetSubscribersCount } from '~/api/modules/channels'
  import { Prop } from '~/node_modules/vue-property-decorator'

  @Component({})
  export default class ChannelPage extends Vue {
    @Prop({default: null}) readonly channel!: Channel

    subscribersCount: number = 0;
    isSubscribed: boolean = false;
    loaded: boolean = false

    async mounted() {
      let data = await ChannelGetSubscribersCount(this.channel.id!);
      this.subscribersCount = data.subscribers_count;
      this.isSubscribed = data.is_subscribed;

    }
  }
</script>
