<template>
  <div ref="main">
    <c-form-v2 box class="dashboard-page__section" :handler="save" :initial-values="channel">
      <template #default="{ values }">
        <design-editor v-model="values.colors_scheme" :channel="channel" :data="values"/>
      </template>
    </c-form-v2>
    <div class="vertical-delimiter"></div>
    <logos-editor :channel="channel"/>
  </div>
</template>
<script lang="ts" setup>
import DesignEditor from '@/components/dashboard/design/DesignEditor.vue';
import LogosEditor from "@/components/dashboard/design/LogosEditor.vue";

const { t } = useI18n();
const { request } = useApi();

const props = defineProps<{
  channel: Channels.Item;
}>();

useHead(() => ({
  title: t('dashboard.design.heading')
}));

const save = (values: Partial<Channels.Item>) => {
  return request.put('/channels/:id', { body: values }, { id: props.channel.id });
}
</script>
