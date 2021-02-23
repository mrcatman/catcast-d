<template>
  <div>

  </div>
</template>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import Channel from '~/types/Channel'
import { Prop } from '~/node_modules/vue-property-decorator'
import { ChannelPermissions } from '~/helpers/channelPermissions'
import { ChatGetData } from '~/api/modules/chat'

@Component({

})
export default class Chat extends Vue {
  @Prop({required: true}) readonly channel!: Channel
  @Prop({required: true}) readonly permissions!: Array<ChannelPermissions>

  async mounted() {
    const chatData = await ChatGetData(this.channel.id!);
    console.log(chatData);
    const config = this.$accessor.modules.site?.config || {};
    const ws = new WebSocket(
      `ws://${this.channel.domain || config.domain}/api/chat/${this.channel.id}/realtime${chatData.connect_key ? '?connect_key=' + chatData.connect_key : ''}`
    );
    ws.onmessage = (e: MessageEvent) => {
      console.log(e.data);
    }
  }
}
</script>
