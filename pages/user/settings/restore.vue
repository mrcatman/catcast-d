<template>
  <div class="settings-page__restore">
    <c-box no-padding>
      <template slot="title">
        {{$t('settings.restore.deleted_channels')}}
      </template>
      <template slot="main">
        <c-list>
          <div class="centered" v-if="loading.deleted">
            <c-preloader  />
          </div>
          <c-nothing-found small v-else-if="deleted.length === 0"></c-nothing-found>
          <c-list-item small :picture="channel.logo" v-for="channel in deleted" :key="channel.id">
            <template slot="captions">
              <div class="list-item__title">{{channel.name}}</div>
            </template>
            <template slot="buttons">
              <c-button color="green" :loading="channel._loading" @click="restore(channel)">{{$t('settings.restore.restore_channel')}}</c-button>
            </template>
          </c-list-item>
        </c-list>
      </template>
    </c-box>

    <c-box no-padding>
      <template slot="title">
        {{$t('settings.restore.left_channels')}}
      </template>
      <template slot="main">
        <c-list >
          <div class="centered" v-if="loading.left">
            <c-preloader  />
          </div>
          <c-nothing-found small v-else-if="left.length === 0"></c-nothing-found>
          <c-list-item small :picture="channel.logo"  class="list-container__inner" v-for="channel in left" :key="channel.id">
            <template slot="captions">
              <div class="list-item__title">{{channel.name}}</div>
            </template>
            <template slot="buttons">
              <c-button color="green" :loading="channel._loading" @click="getBack(channel)">{{$t('settings.restore.get_back')}}</c-button>
            </template>
          </c-list-item>
        </c-list>
      </template>
    </c-box>

  </div>
</template>
<style>
.settings-page__restore {
  padding: 0 2em;
}
</style>
<script>
  export default {
    async mounted() {
      this.deleted = await this.$api.get('channels/deleted');
      this.loading.deleted = false;
      this.left = await this.$api.get('channels/left');
      this.loading.left = false;
    },
    data() {
      return {
        loading: {
          left: true,
          deleted: true
        },
        deleted: [],
        left: [],
      }
    },
    methods: {
      restore(channel) {
        this.$set(channel, '_loading', true);
        this.$api.post(`/channels/${channel.id}/restore`).then(() => {
          this.$set(channel, '_loading', false);
          this.deleted.splice(this.deleted.indexOf(channel), 1);
        })
      },
      getBack(channel) {
        this.$set(channel, '_loading', true);
        this.$api.post(`/channels/${channel.id}/team/return`).then(() => {
          this.$set(channel, '_loading', false);
          this.left.splice(this.left.indexOf(channel), 1);
        })
      },
    }
  }
</script>
