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
  // todo: manual recording

  <c-box>
    <template slot="main">
      {{activeBroadcast}}
      <c-button color="green" icon="settings" @click="editCurrentBroadcast()">{{$t('dashboard.broadcast.edit')}}</c-button>
    </template>
  </c-box>

  <c-box no-padding>
    <template slot="main">

      <c-thumbs-list :config="listConfig">
        <template slot="before_filters">
          <c-button color="green" icon="fa-plus" @click="createNewBroadcast()">{{$t('dashboard.broadcast.create')}}</c-button>
        </template>
        <template slot="after_heading">
          <c-tabs v-model="type" :data="types" />
        </template>
        <template slot="item" slot-scope="props">
          <c-list-item>
            <template slot="captions">
              {{props.item}}
            </template>
          </c-list-item>
        </template>
      </c-thumbs-list>
    </template>
  </c-box>

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
import NotificationItem from "@/components/layout/notifications/NotificationItem.vue";
export default {
  head() {
    return {
      title: this.$t('dashboard.broadcast._title')
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

    },
    editCurrentBroadcast() {

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
