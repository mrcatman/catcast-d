<template>
  <div class="controls-page dashboard">
    <div class="controls-page__menu">
      <div class="controls-page__menu__inner">
        <ChannelLink :channel="channel" class="dashboard__channel">
          <div class="dashboard__channel__texts">
            <div class="dashboard__channel__name">{{channel.name}}</div>
            <div class="dashboard__channel__ap-handle">{{channel.activitypub_handle}}</div>
          </div>
          <div class="dashboard__channel__logo" v-if="channel.logo" :style="{backgroundImage: `url(${channel.logo.full_url})`}"></div>
        </ChannelLink>
        <nuxt-link class="controls-page__menu__item" :to="`/dashboard/${channel.id}/main`">{{$t('dashboard.menu.common')}}</nuxt-link>
        <nuxt-link v-if="permissions.indexOf(UserChannelPermissions.FULL_ADMIN) !== -1" class="controls-page__menu__item" :to="`/dashboard/${channel.id}/team`">{{$t('dashboard.menu.team')}}</nuxt-link>
        <nuxt-link v-if="permissions.indexOf(UserChannelPermissions.BROADCAST) !== -1" class="controls-page__menu__item" :to="`/dashboard/${channel.id}/broadcast`">{{$t('dashboard.menu.broadcast')}}</nuxt-link>
      </div>
    </div>
    <div class="controls-page__content">
      <div class="controls-page__content__inner" v-if="loaded">
        <nuxt-child :channel="channel" :permissions="permissions"></nuxt-child>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .dashboard {
    &__channel {
      cursor: pointer;
      margin: 0 0 1em;
      font-weight: 300;
      display: inline-flex;
      align-items: center;
      text-align: right;
      text-decoration: none;
      &__logo {
        width: 4em;
        height: 4em;
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
        margin: 0 0 0 1em;
      }

      &__name {
        font-size: 1.25em;
        font-weight: 400;
      }
    }
  }
</style>
<script lang="ts">
  import { Component, Vue } from 'nuxt-property-decorator'
  import ChannelLink from '~/components/layout/ChannelLink.vue';
  import Channel from '~/types/Channel'
  import { ChannelGetById, ChannelGetPermissions } from '~/api/modules/channels'
  import { UserChannelPermissions } from '~/helpers/permissions'
  @Component({
    middleware: 'auth',
    components: {
      ChannelLink
    }
  })
  export default class DashboardContainerPage extends Vue {
    UserChannelPermissions = UserChannelPermissions;
    channel: Channel = {} as Channel;
    permissions: Array<string> = [] as Array<string>;
    error = null;
    loaded: boolean = false;

    async fetch() {
      try {
        let id = parseInt(this.$route.params.id);
        this.channel = await ChannelGetById(id);
        this.permissions = await ChannelGetPermissions(id);
        if (this.permissions.length === 0) {
          this.$router.push('/dashboard');
        }
        this.loaded = true;
      } catch (e) {
        console.log(e);
      }
    }
  }
</script>
