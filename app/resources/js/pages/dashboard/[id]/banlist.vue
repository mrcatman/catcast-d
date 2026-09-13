<template>
  <div class="dashboard__ban-list">
    <c-box no-padding>
      <template #title>
        <c-tabs :data="tabs" v-model="currentTab"/>
      </template>
      <template #main>
        <div v-if="currentTab === 'users'">
          <div class="dashboard__ban-list__description">
            {{ $t('dashboard.banlist.ban_user_description') }}
          </div>
          <c-form-v2
              ref="usersForm"
              class="dashboard__ban-list__form"
              :handler="addUser"
              :initial-values="defaultUserBan"
              :show-submit="false"
              @success="onUserAddSuccess"
          >
            <template #default="{ values, errors }">
              <c-row align="start">
                <c-col>
                  <c-autocomplete
                      v-model="values.user_id"
                      :errors="errors.user_id"
                      autocomplete-key="id"
                      autocomplete-value="username"
                      url="users/autocomplete"
                      :title="$t('global.username')"
                  />
                </c-col>
                <c-col>
                  <c-select
                      v-model="values.ban_duration"
                      :errors="errors.ban_duration"
                      :title="$t('dashboard.banlist.select_ban_duration')"
                      :options="getBanDurationOptions(values)"
                      @change="onBanDurationChange(values)"
                  />
                </c-col>
                <c-col v-if="values.ban_duration === -1" mobile-full-width>
                  <c-datetime-picker
                      :title="$t('dashboard.banlist.ban_duration_manual')"
                      v-model="values.banned_till"
                      :errors="errors.banned_till"
                  />
                </c-col>
                <c-col :grow="2" mobile-full-width>
                  <c-input v-model="values.reason" :errors="errors.reason" :title="$t('dashboard.banlist.reason')"/>
                </c-col>
                <c-col with-button>
                  <c-button
                      :disabled="!values.user_id"
                      :loading="usersFormRef?.loading"
                      @click="usersFormRef?.submit()"
                  >
                    {{ $t('dashboard.banlist.add_user') }}
                  </c-button>
                </c-col>
              </c-row>
            </template>
          </c-form-v2>
          <c-thumbs-list ref="usersList" :config="usersListConfig">
            <template #item="{ item }">
              <c-list-item :picture="item.user.avatar" :picture-square="true">
                <template #captions>
                  <a class="list-item__title">{{ item.user.username }}</a>
                  <div class="list-item__under-title">
                    <c-tooltip position="bottom-right">
                      <div v-if="item.banned_by_user">
                        {{ $t('dashboard.banlist.banned_by', {user: item.banned_by_user.username}) }}
                      </div>
                      <div>{{ $t('dashboard.banlist.banned_at', {date: formatDate(item.created_at)}) }}</div>
                    </c-tooltip>
                    <span v-if="!item.banned_till">{{ $t('dashboard.banlist.banned_forever') }}</span>
                    <span v-else>
                      {{ $t('dashboard.banlist.banned_till', {date: formatDate(item.banned_till)}) }}
                    </span>
                  </div>
                  <div
                      v-if="item.reason && item.reason !== ''"
                      class="list-item__under-title list-item__under-title--small"
                  >
                    {{ $t('dashboard.banlist.reason_text', {reason: item.reason}) }}
                  </div>
                </template>
                <template #buttons>
                  <c-button @click="deleteUser(item)" color="red">{{ $t('global.delete') }}</c-button>
                </template>
              </c-list-item>
            </template>
          </c-thumbs-list>
        </div>

        <div v-if="currentTab === 'ip'">
          <div class="dashboard__ban-list__description">
            {{ $t('dashboard.banlist.ban_ip_description') }}
          </div>
          <c-form-v2
              ref="ipForm"
              class="dashboard__ban-list__form"
              :handler="addIP"
              :initial-values="defaultIPBan"
              :show-submit="false"
              @success="onIPAddSuccess"
          >
            <template #default="{ values, errors }">
              <c-row align="start">
                <c-col>
                  <c-input
                      v-model="values.ip_address"
                      :errors="errors.ip_address"
                      :title="$t('dashboard.banlist.ip_address')"
                  />
                </c-col>
                <c-col>
                  <c-select
                      v-model="values.ban_duration"
                      :errors="errors.ban_duration"
                      :title="$t('dashboard.banlist.select_ban_duration')"
                      :options="getBanDurationOptions(values)"
                      @change="onBanDurationChange(values)"
                  />
                </c-col>
                <c-col v-if="values.ban_duration === -1" mobile-full-width>
                  <c-datetime-picker
                      :title="$t('dashboard.banlist.ban_duration_manual')"
                      v-model="values.banned_till"
                      :errors="errors.banned_till"
                  />
                </c-col>
                <c-col :grow="2" mobile-full-width>
                  <c-input v-model="values.reason" :errors="errors.reason" :title="$t('dashboard.banlist.reason')"/>
                </c-col>
                <c-col with-button>
                  <c-button
                      :disabled="!values.ip_address"
                      :loading="ipFormRef?.loading"
                      @click="ipFormRef?.submit()"
                  >
                    {{ $t('dashboard.banlist.add_user') }}
                  </c-button>
                </c-col>
              </c-row>
            </template>
          </c-form-v2>
          <c-thumbs-list ref="ipList" :config="ipListConfig">
            <template #item="{ item }">
              <c-list-item>
                <template #captions>
                  <a class="list-item__title">{{ item.ip_address }}</a>
                  <div class="list-item__under-title">
                    <c-tooltip position="bottom-right">
                      <div v-if="item.banned_by_user">
                        {{ $t('dashboard.banlist.banned_by', {user: item.banned_by_user.username}) }}
                      </div>
                      <div>{{ $t('dashboard.banlist.banned_at', {date: formatDate(item.created_at)}) }}</div>
                    </c-tooltip>
                    <span v-if="!item.banned_till">{{ $t('dashboard.banlist.banned_forever') }}</span>
                    <span v-else>
                      {{ $t('dashboard.banlist.banned_till', {date: formatDate(item.banned_till)}) }}
                    </span>
                  </div>
                  <div
                      v-if="item.reason && item.reason !== ''"
                      class="list-item__under-title list-item__under-title--small"
                  >
                    {{ $t('dashboard.banlist.reason_text', {reason: item.reason}) }}
                  </div>
                </template>
                <template #buttons>
                  <c-button @click="deleteIP(item)" color="red">{{ $t('global.delete') }}</c-button>
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
<script lang="ts" setup>
import { type Tab } from '@/components/ui/Tabs.vue';
import { type ListConfig } from '@/composables/usePaginatedData';

const { t } = useI18n();
const { request } = useApi();
const { formatDate } = useDates();
const { newAlert } = useAlertsStore();

const props = defineProps<{
  channel: Channels.Item;
}>();

useHead(() => ({
  title: t('dashboard.banlist.heading')
}));

const currentTab = ref<'users' | 'ip'>('users');

const usersFormRef = useTemplateRef('usersForm');
const ipFormRef = useTemplateRef('ipForm');
const usersListRef = useTemplateRef('usersList');
const ipListRef = useTemplateRef('ipList');

const tabs = computed<Tab[]>(() => [
  { id: 'users', name: t('dashboard.banlist.ban_user') },
  { id: 'ip', name: t('dashboard.banlist.ban_ip') },
]);

const defaultUserBan: Bans.UserBanBody = {
  user_id: null,
  reason: '',
  ban_duration: 86400,
  banned_till: null,
};

const defaultIPBan: Bans.IPBanBody = {
  ip_address: '',
  reason: '',
  ban_duration: 86400,
  banned_till: null,
};

const usersListConfig = computed<ListConfig<Bans.UserBanItem>>(() => ({
  view: 'list',
  infiniteScroll: true,
  search: true,
  innerScroll: true,
  noPadding: true,
  handler: (params) => request.get('/channels/:id/bans', {
    query: params
  }, {
    id: props.channel.id
  })
}));

const ipListConfig = computed<ListConfig<Bans.IPBanItem>>(() => ({
  view: 'list',
  infiniteScroll: true,
  search: true,
  innerScroll: true,
  noPadding: true,
  handler: (params) => request.get('/channels/:id/ip-bans', {
    query: params
  }, {
    id: props.channel.id
  })
}));

const getBanDurationOptions = (values: Bans.UserBanBody | Bans.IPBanBody) => {
  const options = [
    { name: t('dashboard.banlist.options.1_day'), value: 86400 },
    { name: t('dashboard.banlist.options.1_week'), value: 7 * 86400 },
    { name: t('dashboard.banlist.options.1_month'), value: 30 * 86400 },
    { name: t('dashboard.banlist.options.forever'), value: null },
  ];

  if (values.ban_duration !== -1 || !values.banned_till) {
    options.push({ name: t('dashboard.banlist.options.select_manually'), value: -1 });
  } else {
    options.push({
      name: t('dashboard.banlist.select_datetime_manual_till', { date: formatDate(values.banned_till, 'full', true) }),
      value: -1
    });
  }

  return options;
}

const onBanDurationChange = (values: Bans.UserBanBody | Bans.IPBanBody) => {
  values.banned_till = values.ban_duration === -1
      ? Math.floor(new Date().getTime() / 1000) + 86400
      : null;
}

const addUser = (values: Bans.UserBanBody) => {
  return request.post('/channels/:id/bans', { body: values }, { id: props.channel.id });
}

const addIP = (values: Bans.IPBanBody) => {
  return request.post('/channels/:id/ip-bans', { body: values }, { id: props.channel.id });
}

const onUserAddSuccess = () => {
  usersListRef.value?.reload();
  if (usersFormRef.value) {
    usersFormRef.value.values.user_id = null;
    usersFormRef.value.values.reason = '';
  }
  newAlert({
    type: 'success',
    text: t('dashboard.banlist._messages.user_added_to_banlist')
  });
}

const onIPAddSuccess = () => {
  ipListRef.value?.reload();
  if (ipFormRef.value) {
    ipFormRef.value.values.ip_address = '';
    ipFormRef.value.values.reason = '';
  }
  newAlert({
    type: 'success',
    text: t('dashboard.banlist._messages.ip_address_added_to_banlist')
  });
}

const deleteUser = async (item: Bans.UserBanItem) => {
  await request.delete('/channels/:id/bans/:userId', {}, {
    id: props.channel.id,
    userId: item.user_id
  });
  usersListRef.value?.reload();
  newAlert({
    type: 'success',
    text: t('dashboard.banlist._messages.user_deleted_from_banlist')
  });
}

const deleteIP = async (item: Bans.IPBanItem) => {
  await request.delete('/channels/:id/ip-bans/:ip', {}, {
    id: props.channel.id,
    ip: item.ip_address
  });
  ipListRef.value?.reload();
  newAlert({
    type: 'success',
    text: t('dashboard.banlist._messages.ip_address_deleted_from_banlist')
  });
}
</script>
