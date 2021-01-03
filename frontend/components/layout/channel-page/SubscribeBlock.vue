<template>
  <div class="subscribe-block">
    <m-button @click="subscribe" :loading="loading" :count="subscribersCount" :flat="isSubscribed" :icon="isSubscribed ? 'favorite' : 'favorite_border'">{{isSubscribed ? $t('subscribe_block.unsubscribe') : $t('subscribe_block.subscribe') }}</m-button>
  </div>
</template>
<style lang="scss">

</style>
<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'
  import Channel from '~/types/Channel'
  import { ChannelGetSubscribersCount, ChannelSubscribe, ChannelUnsubscribe } from '~/api/modules/channels'
  import { Prop } from '~/node_modules/vue-property-decorator'

  @Component({})
  export default class SubscribeBlock extends Vue {
    @Prop({default: null}) readonly channel!: Channel

    subscribersCount: number = 0;
    isSubscribed: boolean = false;
    loading: boolean = true

    async load() {
      let data = await ChannelGetSubscribersCount(this.channel.id!);
      this.subscribersCount = data.subscribers_count;
      this.isSubscribed = data.is_subscribed;
      this.loading = false;
    }

    async mounted() {
      await this.load();
    }

    async subscribe(): Promise<void> {
      this.loading = true;
      let action = this.isSubscribed ? ChannelUnsubscribe : ChannelSubscribe;
      await action(this.channel.id!);
      await this.load();
    }
  }
</script>
