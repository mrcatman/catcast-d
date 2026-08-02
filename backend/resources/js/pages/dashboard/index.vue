<template>
  <div class="dashboard page-container">
    <c-box no-padding>
      <template #main>
        <c-thumbs-list ref="list" :config="listConfig">
          <template #before_filters>
            <c-button @click="createChannel()" icon="fa-plus-square" color="green">
              {{ $t('dashboard.create.heading') }}
            </c-button>
          </template>
          <template #filters="{filters}">
            <c-tabs v-show="channelTypes.length > 1" :data="channelTypes" v-model="filters.channel_type"/>
          </template>
          <template #item="{item}">
            <c-list-item :to="`/dashboard/${item.id}/info`" :picture="item.logo" :picture-square="true">
              <template #captions>
                <div class="list-item__title">{{ item.name }}</div>
                <div class="list-item__under-title" v-if="!item.is_banned">
                  <c-tag :key="$index" v-for="(key, $index) in getPermissionsList(item)">{{ key }}</c-tag>
                </div>
                <span class="dashboard__channels-list__banned" v-if="item.is_banned">
              {{ !item.is_radio ? $t('dashboard.channel_is_banned_for') : $t('dashboard.radio_is_banned_for') }}
              <strong>{{ item.ban_reason }}</strong>
            </span>
              </template>
              <template #buttons>
                <c-button v-if="!item.is_banned" icon="fas-tachometer-alt" :to="`/dashboard/${item.id}/info`">
                  {{ $t('dashboard.page_types.main') }}
                </c-button>
                <!-- todo: move the button somewhere as it's not as important (?) -->
                <c-button v-if="item.can_leave_team" @click="leaveTeam(item)" flat icon-only icon="exit_to_app"/>
              </template>
            </c-list-item>
          </template>
        </c-thumbs-list>
      </template>
    </c-box>
  </div>
</template>
<script lang="ts" setup>
import { CHANNEL_TYPE_ALL, CHANNEL_TYPE_TV, CHANNEL_TYPE_RADIO, type ChannelType } from "@/constants/entity-types.ts";

import { type Tab } from '@/components/ui/Tabs';
import { type ListConfig } from '@/components/ui/ThumbsList';
import CreateChannel from "@/components/dashboard/CreateChannel";

const {t} = useI18n();
const {allowedChannelTypes} = useConfigStore();
const {request} = useApi();

useHead(() => {
  return {
    title: t('settings.heading')
  }
})

definePageMeta({
  middleware: [
    'auth',
  ]
});


const channelTypes: Array<Tab> = [];
if (allowedChannelTypes[CHANNEL_TYPE_TV] && allowedChannelTypes[CHANNEL_TYPE_RADIO]) {
  channelTypes.push({id: CHANNEL_TYPE_ALL, name: t('global.all')});
}
if (allowedChannelTypes[CHANNEL_TYPE_TV]) {
  channelTypes.push({id: CHANNEL_TYPE_TV, name: t('channels.tv')});
}
if (allowedChannelTypes[CHANNEL_TYPE_RADIO]) {
  channelTypes.push({id: CHANNEL_TYPE_RADIO, name: t('channels.radio')});
}

const listConfig: ListConfig = {
  title: t('dashboard.heading'),
  handler: (params: Api.PaginatedQuery) => request.get('channels', {
    query: {
      ...params,
      with_permissions: true,
    }
  }),
  view: 'list',
  innerScroll: true,
  infiniteScroll: true,
  search: true,
  noPadding: true,
  usePreloadingListItem: true
}

const createChannel = () => {
  this.$store.commit('modals/showStandardModal', {
    confirm: true,
    title: t('dashboard.create.heading'),
    buttonColor: '',
    buttonText: t('dashboard.create.button_text'),
    component: CreateChannel,
    formValues: {
      channel_type: this.allowedChannelTypes[CHANNEL_TYPE_TV] ? CHANNEL_TYPE_TV : CHANNEL_TYPE_RADIO,
      name: '',
      shortname: '',
      tags: []
    },
    fn: async (data) => {
      const {id} = await this.$api.post(`/channels`, data);
      if (id) {
        await this.$router.push(`/dashboard/${id}/info`);
      }
    },
  })
}

const leaveTeam = (channel) => {
  this.$store.commit('modals/showStandardModal', {
    confirm: true,
    title: '',
    text: ('dashboard.leave.confirm'),
    buttonText: t('global.ok'),
    fn: async () => {
      await this.$api.post(`channels/${channel.id}/team/leave`);
      this.$refs.list.reload();
    },
  })
}
const getPermissionsList = (channel: Channels.Item) => {
  if (!channel.permissions) {
    return [];
  }
  return Object.keys(channel.permissions).map(key =>
      t(`channel_permissions.${key}.name`)
  )
}

</script>
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
      border-radius: var(--border-radius);
    }
  }
}
</style>

