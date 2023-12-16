<template>
<div>

  <c-box>
    <template slot="title">
      {{$t('dashboard.broadcast._title')}}
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
  // todo: current/last broadcast
  // todo: settings (category, description)
  // todo: manual recording

      <!--
      <div class="box box--with-header" v-if="!channel.is_radio">
        <div class="box__header">
          {{$t('dashboard.broadcast.record._title')}}
        </div>
        <div class="box__inner">
            <RecordButton :channel_id="channel.id" />
        </div>
      </div>
      -->

  // todo: autoupdate channel model
    <c-box>
      <template slot="title">{{$t('dashboard.broadcast.recording._title')}}</template>
      <template slot="main">
        <c-form method="put" :url="`/channels/${channel.id}`" :initialValues="channel">
          <c-checkbox :title="$t('dashboard.broadcast.recording.record_all')" v-form-input="'additional_settings.recording.record_all'" />
          <c-checkbox v-form-show="'additional_settings.recording.record_all'" :title="$t('dashboard.broadcast.recording.records_visible')" v-form-input="'additional_settings.recording.records_visible'" />
        </c-form>
      </template>
    </c-box>

</div>
</template>
<style lang="scss">

</style>
<script>
import copyTag from '@/components/global/copyTag';
import RecordButton from "../../../components/buttons/RecordButton";
export default {
  head() {
    return {
      title: this.$t('dashboard.broadcast._title')
    }
  },
	async asyncData({app,params}) {
		const key = await app.$api.get(`/channels/${params.id}/stream/key`);
    const servers = await app.$api.get(`/channels/${params.id}/stream/servers`);
		return {key, servers};
	},
	components: {
    RecordButton,
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
