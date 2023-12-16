<template>
  <div class="box box--with-header">
    <c-preloader block  v-if="loading" />
    <div class="box__header box__header--dark">
      <div class="row">
        <div class="col">
          <c-input @change="onNameChange()" v-model="name" :title="$t('admin.channels.name')"/>
        </div>
      </div>
    </div>
    <div class="box__inner admin-panel__content">
      <table class="table">
        <thead>
          <tr>
            <td>{{$t('admin.channels.id')}}</td>
            <td>{{$t('admin.channels.shortname')}}</td>
            <td>{{$t('admin.channels.name')}}</td>
            <td>{{$t('admin.channels.space_occupied')}}</td>
            <td></td>
          </tr>
        </thead>
        <tbody>
          <tr :key="$index" v-for="(channel, $index) in channels.data">
            <td><nuxt-link target="_blank" :to="`/dashboard/${channel.id}/info`">{{channel.id}}</nuxt-link></td>
            <td><nuxt-link target="_blank" :to="`/${channel.shortname}`">{{channel.shortname}}</nuxt-link></td>
            <td>{{channel.name}}</td>
            <td>{{bytesToFileSize(channel.space_occupied)}}</td>
            <td>
              <c-button @click="ban(channel)" v-if="!channel.is_banned">{{$t('admin.channels.ban')}}</c-button>
              <c-button color="red" @click="unban(channel)" v-else>{{$t('admin.channels.unban')}}</c-button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="box__footer">
      <c-pager :data="channels" v-model="currentPage" />
    </div>

    <c-modal v-model="banPanel.visible">
      <div slot="main">
        <div class="modal__input-container">
          <c-input :title="$t('admin.channels.ban_reason')" v-model="banPanel.data.reason"/>
        </div>
      </div>
      <div slot="buttons">
        <div class="buttons-row">
          <c-button @click="setBan()" :loading="banPanel.loading">{{$t('global.ok')}}</c-button>
          <c-button flat @click="banPanel.visible = false">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>

  </div>
</template>
<script>
  const count = 12;
  export default {
    methods: {
      unban(channel) {
        this.$axios.post(`admin/channels/ban`, {status: false, channel_id: channel.id}).then(res => {
          this.$store.commit('NEW_ALERT', res.data);
          if (res.data.status) {
            channel.is_banned = false;
          }
        })
      },
      setBan() {
        this.banPanel.loading = true;
        this.$axios.post(`admin/channels/ban`, {status: true, channel_id: this.banPanel.channel.id, reason: this.banPanel.data.reason}).then(res => {
          this.banPanel.loading = false;
          this.$store.commit('NEW_ALERT', res.data);
          if (res.data.status) {
            this.banPanel.channel.is_banned = true;
          }
        })
      },
      ban(channel) {
        this.banPanel.channel = channel;
        this.banPanel.visible = true;
      },
      onNameChange() {
        if (this.currentPage !== 0) {
          this.currentPage = 0;
        } else {
          this.load();
        }
      },
      async load() {
        this.loading = true;
        let data = (await this.$api.get(`admin/channels?page=${this.currentPage}&count=${count}&name=${this.name}`));
        if (!data.status) {
          this.$store.commit('NEW_ALERT', data);
        } else {
          this.channels = data.data.channels;
        }
        this.loading = false;
      },
      bytesToFileSize(bytes) {
        let gb = Math.round(bytes / (1024*1024*1024) * 1000) / 1000;
        gb = gb  + ' GB';
        return gb;
      }
    },
    watch: {
      currentPage(newPage) {
        this.load();
      }
    },
    async mounted() {
      this.load();
    },
    data() {
      return {
        banPanel: {
          loading: false,
          channel: null,
          visible: false,
          data: {
            reason: ''
          }
        },
        loading: true,
        currentPage: 1,
        name: '',
        channels: {
          data: []
        }
      }
    }
  }
</script>
