<template>
  <layout-with-left-menu class="settings-page">
    <template #tabs>
      <c-tabs :vertical="!_isMobile" :data="tabs"/>
    </template>
    <template #main>
      <div class="page-container settings-page__container">
        <nuxt-page/>
      </div>
    </template>
  </layout-with-left-menu>
</template>
<style lang="scss">
.settings-page {
  &__container {
    padding: 0 2.5em;
    @media screen and (max-width: 768px) {
      padding: 0;
    }
  }
}
</style>
<script lang="ts" setup>
import LayoutWithLeftMenu from "@/components/LayoutWithLeftMenu";
import isMobile from "@/helpers/isMobile";

const {t} = useI18n();

const router = useRouter();
const route = useRoute();

const _isMobile = isMobile();


const autoRedirect = (() => {
  if (route.name === 'user-settings') {
    router.push(`/user/settings/personal`);
  }
})

watch(route, autoRedirect);
onMounted(autoRedirect);

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

const tabs = [
  {id: 'personal', link: '/user/settings/personal', name: t('settings.personal')},
  {id: 'password', link: '/user/settings/password', name: t('settings.password.heading')},
  {id: 'notifications', link: '/user/settings/notifications', name: t('settings.notifications')},
  {id: 'blacklist', link: '/user/settings/blacklist', name: t('settings.blacklist')},
  {id: 'privacy', link: '/user/settings/privacy', name: t('settings.privacy')},
  {id: 'social', link: '/user/settings/social', name: t('settings.social')},
  {id: 'restore', link: '/user/settings/restore', name: t('settings.restore.heading')},
];
</script>
