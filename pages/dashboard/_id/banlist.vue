<template>
  <div class="dashboard__ban-list">
    <c-box no-padding>
      <template slot="title">
        <c-tabs :data="tabs" v-model="currentTab" />
      </template>
      <template slot="main">
        <div v-if="currentTab === 'users'">
          <div class="dashboard__ban-list__description">
            {{ $t('dashboard.banlist.ban_user_description') }}
          </div>
          <c-form v-model="usersForm" class="dashboard__ban-list__form" ref="users_form" :url="`/channels/${channel.id}/bans`" :hide-submit="true" :hide-status="true" @success="onUserAddSuccess">
            <c-row align="start">
              <c-col>
                <c-autocomplete v-form-input="'user_id'" autocomplete-key="id" autocomplete-value="username" url="users/autocomplete" :title="$t('global.username')"/>
              </c-col>
              <c-col>
                <c-select v-form-input="'ban_duration'" :title="$t('dashboard.banlist.select_ban_duration')" :options="getBanDurationOptions(usersForm)" @change="onBanDurationChange(usersForm)"/>
              </c-col>
              <c-col v-if="usersForm.ban_duration === -1" mobile-full-width>
                <c-datetime-picker :title="$t('dashboard.banlist.ban_duration_manual')"  v-form-input="'banned_till'" />
              </c-col>
              <c-col :grow="2" mobile-full-width>
                <c-input v-form-input="'reason'" :title="$t('dashboard.banlist.reason')"/>
              </c-col>
              <c-col with-button v-if="$refs.users_form">
                <c-button :disabled="!usersForm.user_id" :loading="$refs.users_form.loading" @click="$refs.users_form.submit()">{{ $t('dashboard.banlist.add_user') }}</c-button>
              </c-col>
            </c-row>
          </c-form>
          <c-thumbs-list ref="users_list" :config="usersListConfig">
            <template slot="item" slot-scope="props">
              <c-list-item :picture="props.item.user.avatar" :picture-square="true">
                <template slot="captions">
                  <a class="list-item__title">{{ props.item.user.username }}</a>
                  <div class="list-item__under-title">
                    <c-tooltip position="bottom-right">
                      <div v-if="props.item.banned_by_user">
                        {{ $t('dashboard.banlist.banned_by', {user: props.item.banned_by_user.username}) }}
                      </div>
                      <div>{{ $t('dashboard.banlist.banned_at', {date: formatFullDate(props.item.created_at)}) }}</div>
                    </c-tooltip>
                    <span v-if="!props.item.banned_till">{{ $t('dashboard.banlist.banned_forever') }}</span>
                    <span v-else>{{ $t('dashboard.banlist.banned_till', {date: formatFullDate(props.item.banned_till)}) }}</span>
                  </div>
                  <div v-if="props.item.reason && props.item.reason !== ''"
                       class="list-item__under-title list-item__under-title--small">
                    {{ $t('dashboard.banlist.reason_text', {reason: props.item.reason}) }}
                  </div>
                </template>
                <template slot="buttons">
                  <c-button @click="deleteUser(props.item)" color="red">{{ $t('global.delete') }}</c-button>
                </template>
              </c-list-item>
            </template>
          </c-thumbs-list>
        </div>
        <div v-if="currentTab === 'ip'">
          <div class="dashboard__ban-list__description">
            {{ $t('dashboard.banlist.ban_ip_description') }}
          </div>
          <c-form v-model="ipForm" class="dashboard__ban-list__form" ref="ip_form" :url="`/channels/${channel.id}/ip-bans`" :hide-submit="true" :hide-status="true" @success="onIPAddSuccess">
            <c-row align="start">
              <c-col>
                <c-input v-form-input="'ip_address'" :title="$t('dashboard.banlist.ip_address')"/>
              </c-col>
              <c-col>
                <c-select v-form-input="'ban_duration'" :title="$t('dashboard.banlist.select_ban_duration')" :options="getBanDurationOptions(ipForm)" @change="onBanDurationChange(ipForm)"/>
              </c-col>
              <c-col v-if="ipForm.ban_duration === -1" mobile-full-width>
                <c-datetime-picker :title="$t('dashboard.banlist.ban_duration_manual')"  v-form-input="'banned_till'" />
              </c-col>
              <c-col :grow="2" mobile-full-width>
                <c-input v-form-input="'reason'" :title="$t('dashboard.banlist.reason')"/>
              </c-col>
              <c-col with-button v-if="$refs.ip_form">
                <c-button :disabled="!ipForm.ip_address" :loading="$refs.ip_form.loading" @click="$refs.ip_form.submit()">{{ $t('dashboard.banlist.add_user') }}</c-button>
              </c-col>
            </c-row>
          </c-form>
          <c-thumbs-list ref="ip_list" :config="ipListConfig">
            <template slot="item" slot-scope="props">
              <c-list-item >
                <template slot="captions">
                  <a class="list-item__title">{{ props.item.ip_address }}</a>
                  <div class="list-item__under-title">
                    <c-tooltip position="bottom-right">
                      <div v-if="props.item.banned_by_user">
                        {{ $t('dashboard.banlist.banned_by', {user: props.item.banned_by_user.username}) }}
                      </div>
                      <div>{{ $t('dashboard.banlist.banned_at', {date: formatFullDate(props.item.created_at)}) }}</div>
                    </c-tooltip>
                    <span v-if="!props.item.banned_till">{{ $t('dashboard.banlist.banned_forever') }}</span>
                    <span v-else>{{ $t('dashboard.banlist.banned_till', {date: formatFullDate(props.item.banned_till)}) }}</span>
                  </div>
                  <div v-if="props.item.reason && props.item.reason !== ''"
                       class="list-item__under-title list-item__under-title--small">
                    {{ $t('dashboard.banlist.reason_text', {reason: props.item.reason}) }}
                  </div>
                </template>
                <template slot="buttons">
                  <c-button @click="deleteIP(props.item)" color="red">{{ $t('global.delete') }}</c-button>
                </template>
              </c-list-item>
            </template>
          </c-thumbs-list>
        </div>
      </template>
    </c-box>


  </div>
</template>
<style lang="scss">
.dashboard__ban-list {
  &__form {
    padding: .5em 1em;
    border-bottom: 1px solid var(--border-color);
  }
  &__description {
    padding: 1em 1em 0;
  }
}
</style>
<script>
import {formatFullDate} from '@/helpers/dates';

const baseListConfig = {
  view: 'list',
  paginate: true,
  infiniteScroll: true,
  search: true,
  innerScroll: true,
  noPadding: true
};

export default {
  head() {
    return {
      title: this.$t('dashboard.banlist.heading')
    }
  },
  props: {
    channel: {
      type: Object,
      required: true
    }
  },

  computed: {
    usersListConfig() {
      return {
        ...baseListConfig,
        url: `/channels/${this.channel.id}/bans`,
      }
    },
    ipListConfig() {
      return {
        ...baseListConfig,
        url: `/channels/${this.channel.id}/ip-bans`,
      }
    },
    tabs() {
      return [
        {id: 'users', name: this.$t('dashboard.banlist.ban_user')},
        {id: 'ip', name: this.$t('dashboard.banlist.ban_ip')},
      ]
    },

  },
  data() {
    return {
      currentTab: 'users',
      usersForm: {},
      ipForm: {},
      loading: false,
    }
  },
  mounted() {
    this.usersForm.banned_till = this.getBanDurationOptions(this.usersForm)[0].value;
  },
  methods: {
    formatFullDate,
    getBanTime(type) {
      return this.forms[type].values.banned_till === -1 ? Math.floor((new Date(this.forms[type].custom_ban_time).getTime() - new Date().getTime()) / 1000) : this.forms[type].values.banned_till;
    },
    getBanDurationOptions(form) {
      const options = [
        {name: this.$t('dashboard.banlist._options.1_day'), value: 86400},
        {name: this.$t('dashboard.banlist._options.1_week'), value: 7 * 86400},
        {name: this.$t('dashboard.banlist._options.1_month'), value: 30 * 86400},
        {name: this.$t('dashboard.banlist._options.forever'), value: null},
      ]
      if (form.banned_till !== -1) {
        options.push({name: this.$t('dashboard.banlist._options.select_manually'), value: -1});
      } else {
        options.push({
          name: this.$t('dashboard.banlist.select_datetime_manual_till', {date: formatFullDate(form.banned_till)}),
          value: -1
        });
      }
      return options;
    },
    onBanDurationChange(form) {
      if (form.ban_duration === -1) {
        this.$set(form, 'banned_till', new Date().getTime() / 1000 + 86400);
      } else {
        this.$set(form, 'banned_till', null);
      }
    },
    onUserAddSuccess() {
      this.$refs.users_list.reload();
      this.usersForm.user_id = null;
      this.usersForm.reason = '';
      this.$store.commit('NEW_ALERT', {
        status: 1,
        text: this.$t('dashboard.banlist._messages.user_added_to_banlist')
      })
    },
    onIPAddSuccess() {
      this.$refs.ip_list.reload();
      this.ipForm.ip_address = '';
      this.ipForm.reason = '';
      this.$store.commit('NEW_ALERT', {
        status: 1,
        text: this.$t('dashboard.banlist._messages.ip_address_added_to_banlist')
      })
    },
    deleteUser(data) {
      this.$api.delete(`/channels/${this.channel.id}/bans/${data.user_id}`).then(() => {
        this.$refs.users_list.load();
        this.$store.commit('NEW_ALERT', {
          status: 1,
          text: this.$t('dashboard.banlist._messages.user_deleted_from_banlist')
        })
      })
    },
    deleteIP(data) {
      this.$api.delete(`/channels/${this.channel.id}/ip-bans/${data.ip_address}`).then(res => {
        this.$refs.ip_list.load();
        this.$store.commit('NEW_ALERT', {
          status: 1,
          text: this.$t('dashboard.banlist._messages.ip_address_deleted_from_banlist')
        })
      })
    },
  }
}
</script>
