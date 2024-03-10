<template>
<div class="dashboard page-container">
  <c-box no-padding>
    <template slot="main">
      <c-thumbs-list ref="list" :config="listConfig">
        <template slot="before_filters">
          <c-button @click="createChannel()" icon="fa-plus-square" color="green">{{$t('dashboard.create.heading')}}</c-button>
        </template>
        <template slot="after_heading">
          <c-tabs v-show="channelTypes.length > 1" :data="channelTypes" v-model="channelType" />
        </template>
        <template slot="item" slot-scope="props">
          <c-list-item :to="`/dashboard/${props.item.id}/info`" :picture="props.item.logo" :picture-square="true">
            <template slot="captions">
              <div class="list-item__title">{{props.item.name}}</div>
              <div class="list-item__under-title" v-if="!props.item.is_banned">
                <c-tag :key="$index" v-for="(key, $index) in getPermissionsList(props.item)">{{key}}</c-tag>
              </div>
              <span class="dashboard__channels-list__banned" v-if="props.item.is_banned">
              {{!props.item.is_radio ? $t('dashboard.channel_is_banned_for') : $t('dashboard.radio_is_banned_for')}}
              <strong>{{props.item.ban_reason}}</strong>
            </span>
            </template>
            <template slot="buttons">
              <c-button v-if="!props.item.is_banned" icon="fas-tachometer-alt" :to="`/dashboard/${props.item.id}/info`">{{$t('dashboard.page_types.main')}}</c-button>
              <c-button v-if="props.item.can_leave_team" @click="leaveTeam(props.item)" flat icon-only icon="exit_to_app"/>
            </template>
          </c-list-item>
        </template>
      </c-thumbs-list>
    </template>
  </c-box>
</div>
</template>
<style lang="scss">
.dashboard {

  @media screen and (max-width: 768px) {
    overflow-x: hidden;
  }
  &__channels-list {
    &__banned {
      background: var(--red);
      font-size: .875em;
      padding: .25em .5em;
      border-radius: .25em;
    }
  }
}
</style>
<script>
import {mapGetters} from "vuex";

import {CHANNEL_TYPE_ALL, CHANNEL_TYPE_TV, CHANNEL_TYPE_RADIO} from "@/constants/entity-types";

import CreateChannel from "@/components/dashboard/CreateChannel.vue";

export default {
  middleware: "auth",
  computed: {
    ...mapGetters('config', ['allowedChannelTypes']),
    listConfig() {
      return {
        title: this.$t('dashboard.title'),
        url: this.url,
        view: 'list',
        paginate: true,
        innerScroll: true,
        infiniteScroll: true,
        search: true,
        noPadding: true,
        usePreloadingListItem: true
      }
    },
    url() {
      return `/channels/my?${this.channelType !== '' ? '&type=' + this.channelType : ''}`;
    },
    channelTypes() {
      const types = [];
      if (this.allowedChannelTypes[CHANNEL_TYPE_TV] && this.allowedChannelTypes[CHANNEL_TYPE_RADIO]) {
        types.push({ id: CHANNEL_TYPE_ALL, name: this.$t('dashboard.channel_types.all')});
      }
      if (this.allowedChannelTypes[CHANNEL_TYPE_TV]) {
        types.push({ id: CHANNEL_TYPE_TV, name: this.$t('dashboard.channel_types.tv')});
      }
      if (this.allowedChannelTypes[CHANNEL_TYPE_RADIO]) {
        types.push({ id: CHANNEL_TYPE_RADIO, name: this.$t('dashboard.channel_types.radio')});
      }
      return types;
    }
  },
  head() {
    return {
      title: this.$t('dashboard.title')
    }
  },
  data() {
    return {
      currentPage: 1,
      loading: false,
      channelType: 'all',
    };
  },
  mounted() {
    this.channelType = this.channelTypes[0]?.id;
  },
  methods: {
    createChannel() {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: this.$t('dashboard.create.heading'),
        buttonColor: '',
        buttonText: this.$t('dashboard.create.button_text'),
        component: CreateChannel,
        formValues: {
          channel_type: this.allowedChannelTypes[CHANNEL_TYPE_TV] ? CHANNEL_TYPE_TV : CHANNEL_TYPE_RADIO,
          name: '',
          shortname: '',
          tags: []
        },
        fn: async (data) => {
          const { id } = await this.$api.post(`/channels`, data);
          if (id) {
            await this.$router.push(`/dashboard/${id}/info`);
          }
        },
      })
    },
    leaveTeam(channel) {
      this.$store.commit('modals/showStandardModal', {
        confirm: true,
        title: '',
        text: this.$t('dashboard.leave.confirm'),
        buttonText: this.$t('global.ok'),
        fn: async () => {
          await this.$api.post(`channels/${channel.id}/team/leave`);
          this.$refs.list.reload();
        },
      })
    },

    getPermissionsList(channel) {
      return Object.keys(channel.permissions).map(key =>
        this.$t(`channel_permissions.list.${key}.heading`)
      );
    },
  }
};
</script>
