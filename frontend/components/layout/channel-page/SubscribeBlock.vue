<template>
  <div class="subscribe-block">
    <m-button @click="subscribe" :loading="loading" :count="subscribersCount" :flat="isSubscribed" :icon="isSubscribed ? 'favorite' : 'favorite_border'">{{isSubscribed ? $t('subscribe_block.unsubscribe') : $t('subscribe_block.subscribe') }}</m-button>
  </div>
</template>
<style lang="scss">
.subscribe-block {
  position: absolute;
  top: 0;
  right: 0;
}
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
    loading: boolean = true;


    get me() {
      return this.$accessor.modules.auth.me;
    }

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
      if (!this.me) {
        return;
      }
      this.loading = true;
      let action = this.isSubscribed ? ChannelUnsubscribe : ChannelSubscribe;
      try {
        await action(this.channel.id!);
        await this.load();
      } catch (e) {
        this.loading = false;
      }
    }
  }
</script>
