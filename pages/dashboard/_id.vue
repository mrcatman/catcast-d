<template>
  <layout-with-left-menu ref="layout" class="dashboard-page">
    <template slot="tabs">
      <dashboard-menu :channel="channel" :permissions="permissions" />
      <div class="dashboard-page__bottom">
        <div class="dashboard-page__bottom__left">
          <nuxt-link :to="'/'+channel.shortname" target="_blank" class="dashboard-page__bottom__link">
            <div v-if="channel.logo" :style="{backgroundImage: `url(${channel.logo})`}" class="dashboard-page__bottom__channel-logo" ></div>
            <div class="dashboard-page__bottom__channel-name">{{channel.name}}</div>
          </nuxt-link>
        </div>
        <div class="dashboard-page__bottom__right" v-if="channel.is_radio">
          <div class="buttons-row">
            <radio-playback-switch-button :channel="channel"/>
          </div>
        </div>
      </div>
    </template>
    <template slot="main">
		  <nuxt-child :channel="channel" :permissions="permissions" class="layout-with-left-menu__content__inner" />
		</template>
  </layout-with-left-menu>
</template>
<style lang="scss">

.dashboard-page {
  &__bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    @media screen and (max-width: 768px) {
      position: fixed;
      bottom: 0;
      z-index: 100;
      background: var(--box-color);
      width: 100%;
      border-top: 1px solid var(--border-color);
    }
    &__link {
      padding: .5em 1em;
      display: flex;
      align-items: center;
      text-decoration: none;
    }
    &__channel-logo {
      height: 3em;
      width: 3em;
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center center;
      margin-right: .5em;
    }

    &__right {
      display: flex;
    }
  }
}
</style>
<script>
import DashboardMenu from "@/components/dashboard/DashboardMenu";
import RadioPlaybackSwitchButton from "@/components/buttons/RadioPlaybackSwitchButton";
import LayoutWithLeftMenu from "@/components/LayoutWithLeftMenu";
export default {
  middleware: 'auth',
  watch: {
    '$route'() {
      this.$refs.layout.scrollToTop();
    }
  },
  mounted() {
    if (this.$route.name === 'dashboard-id') {
      if (this.items && this.items.length > 0) {
        const page = this.items[0];
        this.$router.push(`/dashboard/${this.channel.id}/${page.link}`);
      }
    }
  },
  async asyncData({ app, params, redirect }) {
    const { permissions } = await app.$api.get(`access-settings/channels/${params.id}`, {
      onError: {
        permissions: {}
      }
    });
    if (Object.keys(permissions).length > 0) {
      const channel = (await app.$api.get(
        `/channels/${params.id}?load_additional_settings=1&do_not_count_stat=1`
      ));
      return {
        channel,
        permissions,
      };
    } else {
      return redirect('/dashboard');
    }
  },
  components: {LayoutWithLeftMenu, DashboardMenu, RadioPlaybackSwitchButton }
};
</script>
