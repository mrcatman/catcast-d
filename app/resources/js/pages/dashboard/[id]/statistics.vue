<template>
  <div>
    <c-box>
      <template #title>
        {{ $t('statistics.heading') }}
      </template>
      <template #main>
        <statistics-block entity-type="channels" :entity-id="channel.id"/>
      </template>
    </c-box>
  </div>
</template>
<script lang="ts" setup>
import StatisticsBlock from "@/components/dashboard/statistics/StatisticsViewer.vue";

const { t } = useI18n();

defineProps<{
  channel: Channels.Item;
}>();

useHead(() => ({
  title: t('statistics.heading')
}));
</script>

<style lang="scss">

</style>
