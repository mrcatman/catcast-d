<template>
  <div>
    <c-box>
      <template slot="title">
        {{$t('dashboard.broadcast.heading')}}
      </template>
      <template slot="main">
        <p v-html="!channel.is_radio ? $t('dashboard.broadcast.settings.description') : $t('dashboard.broadcast.settings.description_radio')"></p>

        <c-row>
          <c-col mobile-full-width>
            <copy-tag  v-for="(server,$index) in servers" :key="$index" :title="$t('dashboard.broadcast.settings.rtmp_url')" :text="server.full_address"/>
          </c-col>
          <c-col mobile-full-width>
            <c-row>
              <c-col>
                <copy-tag password ref="stream_key" :title="!channel.is_radio ? $t('dashboard.broadcast.settings.rtmp_key') : $t('dashboard.broadcast.settings.radio_password')" :text="key.full_key"/>
              </c-col>
              <c-col with-button>
                <c-button :loading="reloading" @click="generateNewKey()" flat>{{$t('dashboard.broadcast.settings.get_new_key')}}</c-button>
              </c-col>
            </c-row>
          </c-col>
        </c-row>
      </template>
    </c-box>


    <c-box>
      <template slot="title">
        {{$t('dashboard.broadcast.active')}}
      </template>
      <template slot="main">
        <active-broadcast-display :channel="channel" :broadcast="activeBroadcast"  />
      </template>
    </c-box>


    <c-box>
      <template slot="title">{{$t('dashboard.broadcast.recording.heading')}}</template>
      <template slot="main">
        <c-form method="put" :url="`/channels/${channel.id}`" :initialValues="channel">
          <c-checkbox :title="$t('dashboard.broadcast.recording.record_all')" v-form-input="'additional_settings.recording.record_all'" />
          <c-checkbox v-form-show="'additional_settings.recording.record_all'" :title="$t('dashboard.broadcast.recording.records_public')" v-form-input="'additional_settings.recording.records_public'" />
        </c-form>
      </template>
    </c-box>

  </div>
</template>
<style lang="scss">

</style>
<script>
import copyTag from '@/components/global/copyTag';
import NotificationItem from "@/components/layout/notifications/NotificationItem.vue";
import ActiveBroadcastDisplay from "@/components/channel/ActiveBroadcastDisplay.vue";
import BroadcastThumb from "@/components/thumbs/BroadcastThumb.vue";
export default {
  head() {
    return {
      title: this.$t('dashboard.broadcast.heading')
    }
  },

  async asyncData({app,params}) {
    const key = await app.$api.get(`/channels/${params.id}/stream/key`);
    const servers = await app.$api.get(`/channels/${params.id}/stream/servers`);
    const activeBroadcast = await app.$api.get(`/channels/${params.id}/broadcasts/active`);
    return {key, servers, activeBroadcast};
  },
  components: {
    BroadcastThumb,
    ActiveBroadcastDisplay,
    NotificationItem,
    copyTag
  },
  props: {
    channel: {
      type: Object,
      required: true
    }
  },
  data () {
    return {
      reloading: false,
    }
  },
  methods: {
    generateNewKey() {
      this.reloading = true;
      this.$api.get(`/channels/${this.channel.id}/stream/key?generate_new_key=true`).then( key => {
        this.key = key;
        this.$refs.stream_key.show();
      }).finally(() => {
        this.reloading = false;
      })
    },
  }
}
</script>
