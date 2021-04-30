<template>
  <div class="subscribe-block">
    <m-button @click="subscribe" :loading="loading" :count="subscribersCount" :flat="isSubscribed" :icon="isSubscribed ? 'person_remove' : 'person_add'">{{isSubscribed ? $t('subscribe_block.unsubscribe') : $t('subscribe_block.subscribe') }}</m-button>
  </div>
</template>
<style lang="scss">
.subscribe-block {
  margin: 1em 0 0;
}
</style>
<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'
  import Channel from '~/types/Channel'
  import User from '~/types/User'
  import { ChannelGetSubscribersCount, ChannelSubscribe, ChannelUnsubscribe } from '~/api/modules/channels'
  import { UserGetSubscribersCount, UserSubscribe, UserUnsubscribe } from '~/api/modules/users'
  import { Prop } from '~/node_modules/vue-property-decorator'

  @Component({})
  export default class SubscribeBlock extends Vue {
    @Prop({default: null}) readonly channel!: Channel
    @Prop({default: null}) readonly user!: User

    subscribersCount: number = 0;
    isSubscribed: boolean = false;
    loading: boolean = true;


    get me() {
      return this.$accessor.modules.auth.me;
    }

    async load() {
       let data = await (this.channel ? ChannelGetSubscribersCount(this.channel.id!) : UserGetSubscribersCount(this.user.id!));
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
      let action = this.channel ? (this.isSubscribed ? ChannelUnsubscribe : ChannelSubscribe) : (this.isSubscribed ? UserUnsubscribe : UserSubscribe);
      try {
        await action(this.channel ? this.channel.id! : this.user.id!);
        await this.load();
      } catch (e) {
        this.loading = false;
      }
    }
  }
</script>
