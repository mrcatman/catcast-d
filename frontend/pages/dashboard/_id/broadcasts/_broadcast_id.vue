<template>
  <c-box>
    <template slot="title">
      {{$t('dashboard.broadcast.statistics', {title: broadcast.title})}}
    </template>
    <template slot="main">
      <statistics-viewer :entity-id="broadcast.id" entity-type="broadcasts" :startParams="startParams" :timeLimits="timeLimits" />
    </template>
  </c-box>

</template>
<script>
import StatisticsViewer from "@/components/dashboard/statistics/StatisticsViewer.vue";

export default {
  components: {StatisticsViewer},
  async asyncData({app, params, redirect}) {
    const broadcast = await app.$api.get(`/broadcasts/${params.broadcast_id}`);
    const timeLimits = {
      start_time: new Date(broadcast.started_at),
      end_time: broadcast.ended_at ? new Date(broadcast.ended_at) : new Date(),
    }
    const startParams = {
      ...timeLimits,
      aggregate: false,
      timespan: 'five_minutes'
    }

    return {
      broadcast,
      startParams,
      timeLimits
    }
  },
  data() {
    return {
      currentTab: 'info',
    }
  },
  head() {
    return {
      title: this.$t('dashboard.broadcast.statistics', {title: this.broadcast.title})
    }
  },
  props: {
    channel: {
      type: Object,
      required: true
    }
  },
}
</script>
