<template>
<div>
  <c-box>
    <template slot="title">
      {{$t('statistics.heading')}}
    </template>
    <template slot="main">
      <statistics-block entity-type="channels" :entity-id="channel.id"/>
    </template>
  </c-box>
</div>
</template>
<script>
  import statisticsBlock from "@/components/dashboard/statistics/StatisticsViewer.vue";
  export default {
    head() {
      return {
        title: this.$t('statistics.heading')
      }
    },
    props: {
      channel: {
        type: Object,
        required: true
      }
    },
    components: {
      statisticsBlock,
    },
  }
</script>

<style lang="scss">

</style>
