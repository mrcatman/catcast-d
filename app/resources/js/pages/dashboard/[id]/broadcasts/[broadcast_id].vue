<template>
  <c-box>
    <template #title>
      {{ $t('dashboard.broadcast.statistics', {title: broadcast?.title}) }}
    </template>
    <template #main>
      <statistics-viewer
          :entity-id="broadcast?.id"
          entity-type="broadcasts"
          :start-params="startParams"
          :time-limits="timeLimits"
      />
    </template>
  </c-box>
</template>
<script lang="ts" setup>
import StatisticsViewer from "@/components/dashboard/statistics/StatisticsViewer.vue";

const { t } = useI18n();
const { request } = useApi();
const { params } = useRoute();

defineProps<{
  channel: Channels.Item;
}>();

const { data: broadcast } = await useAsyncData(
    () => `broadcast-${params.broadcast_id}`,
    () => request.get('/broadcasts/:id', {}, { id: params.broadcast_id })
);

const timeLimits = computed(() => ({
  start_time: broadcast.value?.started_at ? new Date(broadcast.value.started_at) : new Date(),
  end_time: broadcast.value?.ended_at ? new Date(broadcast.value.ended_at) : new Date(),
}));

const startParams = computed(() => ({
  ...timeLimits.value,
  aggregate: false,
  timespan: 'five_minutes'
}));

useHead(() => ({
  title: t('dashboard.broadcast.statistics', { title: broadcast.value?.title })
}));
</script>
