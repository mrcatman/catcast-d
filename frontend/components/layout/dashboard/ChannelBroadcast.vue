<template>
  <div>
    <m-copy-tag v-if="servers.length === 1" :title="$t('dashboard.broadcast.form.url')" readonly :loading="isLoadingServers" v-model="servers[0]" />
    <m-copy-tag v-else :title="$t('dashboard.broadcast.form.urls')" readonly :loading="isLoadingServers" v-model="servers[$index]" v-for="(server, $index) in servers" :key="$index"  />
    <m-copy-tag :title="$t('dashboard.broadcast.form.key')" readonly type="password" :loading="isLoadingKey" v-model="key.full_key" />
    <m-button @click="sendForm" :loading="formIsSubmitting">{{$t('dashboard.broadcast.form.get_new_key')}}</m-button>
  </div>
</template>

<script lang="ts">
  import { Component, Prop } from 'vue-property-decorator'
  import {BaseFormComponent} from '~/components/types/BaseFormComponent'
  import Channel from '~/types/Channel'
  import StreamKey from '~/types/StreamKey'
  import { StreamGetKey, StreamGetServers } from '~/api/modules/stream'

  @Component
  export default class ChannelBroadcast extends BaseFormComponent {

    @Prop({required: true})
    public channel!: Channel;

    isLoadingKey: boolean = true;
    isLoadingServers: boolean = true;
    key: StreamKey = {
      key: '',
      full_key: ''
    };
    servers: Array<String> = [];

    async mounted() {
      this.key = await StreamGetKey(this.channel.id!);
      this.isLoadingKey = false;
      this.servers = await StreamGetServers();
      this.isLoadingServers = false;
    }

    onSubmit(key: StreamKey) {
      this.key = key;
    }

    submit() {
      return StreamGetKey(this.channel.id!, true);
    }

  };
</script>
