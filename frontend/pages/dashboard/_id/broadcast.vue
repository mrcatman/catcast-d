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


      <!--
        // todo: manual recording
         // todo: autoupdate channel model
      <div class="box box--with-header" v-if="!channel.is_radio">
        <div class="box__header">
          {{$t('dashboard.broadcast.record.heading')}}
        </div>
        <div class="box__inner">
            <RecordButton :channel_id="channel.id" />
        </div>
      </div>
      -->


    <c-box>
      <template slot="title">{{$t('dashboard.broadcast.recording.heading')}}</template>
      <template slot="main">
        <c-form method="put" :url="`/channels/${channel.id}`" :initialValues="channel">
          <c-checkbox :title="$t('dashboard.broadcast.recording.record_all')" v-form-input="'additional_settings.recording.record_all'" />
          <c-checkbox v-form-show="'additional_settings.recording.record_all'" :title="$t('dashboard.broadcast.recording.records_public')" v-form-input="'additional_settings.recording.records_public'" />
        </c-form>
      </template>
    </c-box>

  <c-box no-padding>
    <template slot="main">
      <c-thumbs-list ref="list" :config="listConfig">
        <template slot="before_filters">
          <c-button color="green" icon="fa-plus" @click="createNewBroadcast()">{{$t('dashboard.broadcast.create')}}</c-button>
        </template>
        <template slot="after_heading">
          <c-tabs v-model="type" :data="types" />
        </template>
        <template slot="item" slot-scope="props">
          <broadcast-thumb :data="props.item" @reload="$refs.list.reload()" :show-edit-buttons="true" />
        </template>
      </c-thumbs-list>
    </template>
  </c-box>


</div>
</template>
<style lang="scss">

</style>
<script>
import copyTag from '@/components/global/copyTag';
import RecordButton from "../../../components/buttons/RecordButton";
import NotificationItem from "@/components/layout/notifications/NotificationItem.vue";
import BroadcastMetadataEditor from "@/components/dashboard/broadcast/BroadcastMetadataEditor.vue";
import ActiveBroadcastDisplay from "@/components/channel/ActiveBroadcastDisplay.vue";
import BroadcastThumb from "@/components/thumbs/BroadcastThumb.vue";
export default {
  head() {
    return {
      title: this.$t('dashboard.broadcast.heading')
    }
  },
  computed: {
    types() {
      return [
        {id: 'all', name: this.$t('dashboard.broadcast.types.all')},
        {id: 'planned', name: this.$t('dashboard.broadcast.types.planned')},
        {id: 'finished', name: this.$t('dashboard.broadcast.types.finished')},
      ]
    },
    listConfig() {
      return {
        title: this.$t('dashboard.broadcast.list'),
        url: `channels/${this.channel.id}/broadcasts?type=${this.type}`,
        view: 'list',
        paginate: true,
        infiniteScroll: true,
        noPadding: true,
        search: true,
        usePreloadingListItem: true,
      }
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
      type: 'all',
    }
  },
  methods: {
    createNewBroadcast() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        component: BroadcastMetadataEditor,
        buttonColor: '',
        buttonText: this.$t('global.save'),
        title: this.$t('dashboard.broadcast.create'),
        props: {planned: true},
        formValues: {
          tags: []
        },
        fn: async (broadcast) => {
          await this.$api.post(`broadcasts`, {
            ...broadcast,
            channel_id: this.channel.id
          });
          this.$refs.list.reload();
        },
      })
    },
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
