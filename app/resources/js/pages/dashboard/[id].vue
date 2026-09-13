<template>
  <layout-with-left-menu ref="layout" class="dashboard-page">
    <template #tabs>
      <dashboard-menu :channel="data.channel" :permissions="data.permissions" />
      <div class="dashboard-page__bottom">
        <div class="dashboard-page__bottom__left">
          <nuxt-link :to="'/'+data.channel.shortname" target="_blank" class="dashboard-page__bottom__link">
            <div v-if="data.channel.logo" :style="{backgroundImage: `url(${data.channel.logo})`}" class="dashboard-page__bottom__channel-logo" ></div>
            <div class="dashboard-page__bottom__channel-name">{{data.channel.name}}</div>
          </nuxt-link>
        </div>
        <div class="dashboard-page__bottom__right" v-if="data.channel.is_radio">
          <div class="buttons-row">
            <radio-playback-switch-button :channel="data.channel"/>
          </div>
        </div>
      </div>
    </template>
    <template #main>
		  <nuxt-page :channel="data.channel" :permissions="data.permissions" class="layout-with-left-menu__content__inner" />
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
<script lang="ts" setup>
import DashboardMenu from "@/components/dashboard/DashboardMenu";
import RadioPlaybackSwitchButton from "@/components/buttons/RadioPlaybackSwitchButton";
import LayoutWithLeftMenu from "@/components/LayoutWithLeftMenu";

const { request } = useApi();
const { params } = useRoute();

definePageMeta({
  middleware: [
    'auth',
  ]
});


const { data, status } = await useAsyncData('channel', async () => {
  const { permissions } = await request.get('access-settings/channels/:id', {}, {
    id: params.id
  });

  if (Object.keys(permissions).length > 0) {
    const channel = await request.get(`/channels/:id`, {
      query: {
        do_not_count_stat: true
      }
    }, {
      id: params.id
    });
    return {
      channel,
      permissions,
    };
  } else {
  //  return redirect('/dashboard');
  }
})

// export default {
//   middleware: 'auth',
//   watch: {
//     '$route'() {
//       this.$refs.layout.scrollToTop();
//     }
//   },
//   mounted() {
//     if (this.$route.name === 'dashboard-id') {
//       if (this.items && this.items.length > 0) {
//         const page = this.items[0];
//         this.$router.push(`/dashboard/${this.channel.id}/${page.link}`);
//       }
//     }
//   },
//   async asyncData({ app, params, redirect }) {
//
//   },
//   components: {LayoutWithLeftMenu, DashboardMenu, RadioPlaybackSwitchButton }
// };
</script>
