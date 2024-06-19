<template>
  <div class="statistics">
    <c-preloader v-if="!loadedConfig"/>
    <div v-else>

      <c-row align="start">
        <c-col auto-width>
          <c-tabs vertical v-model="type" :data="types"/>
        </c-col>
        <c-col>
          <c-row align="end">
            <c-col auto-width v-show="timespans.length">
              <c-select v-model="params.timespan" :options="timespans"  :title="$t('statistics.timespan')" />
            </c-col>
            <c-col auto-width>
              <c-datetime-picker :min-date="minStartTime" :max-date="maxStartTime" :enable-time="enableTime" :title="$t('statistics.time.start')"
                                 v-model="params.start_time"/>
            </c-col>
            <c-col auto-width>
              <c-datetime-picker :min-date="minEndTime" :max-date="maxEndTime" :enable-time="enableTime" :title="$t('statistics.time.end')"
                                 v-model="params.end_time"/>
            </c-col>
            <c-col auto-width v-if="!selectedTypeConfig.disable_aggregate">
              <c-checkbox :title="$t('statistics.aggregate')" v-model="params.aggregate"/>
            </c-col>
          </c-row>
          <div class="statistics__chart-container">
            <c-preloader block v-show="loading"/>
            <line-chart class="chart statistics__chart" v-if="chartType === 'line'" :colors="colors"
                        :tooltips="tooltips" :labels="labels" :data="data" :display-legend="false"/>
            <statistics-table v-else-if="chartType === 'table'" :chartData="chartData" />
          </div>

          <c-row justify="center" v-if="chartData.length > 1">
            <c-col auto-width v-for="chart in chartData" :key="chart.id">
              <c-checkbox v-model="enabledCharts[chart.id]" :title="$t(chart.name)" :color="getColor(chart.color)"/>
            </c-col>
          </c-row>
          <div class="statistics__numbers" v-if="chartNumbers.length">
            <div class="statistics__number" v-for="(number, $index) in chartNumbers" :key="$index">
              <div class="statistics__number__title">{{ $t(number.name) }}</div>
              <div class="statistics__number__value">{{ number.value }}</div>
            </div>
          </div>
        </c-col>
      </c-row>
    </div>
  </div>
</template>
<style lang="scss">
.statistics {
  &__top {
    margin-bottom: 1em;
  }

  &__chart-container {
    position: relative;
    min-height: 10em;
  }

  &__chart {
    margin-top: 1em;
  }

  &__numbers {
    display: flex;
    margin-top: 1em;
  }
  &__number {
    margin-right: 2.125em;

    &__title {
      font-size: 1.125em;
      font-weight: 400;
    }

    &__value {
      font-size: 2.5em;
      font-weight: bold;
      color: var(--active-color);
    }
  }
}
</style>
<script>
import lineChart from '@/components/dashboard/charts/lineChart.js';
import doughnutChart from '@/components/dashboard/charts/doughnutChart.js';
import StatisticsTable from "@/components/dashboard/statistics/StatisticsTable.vue";

export default {
  computed: {
    minStartTime() {
      return this.timeLimits.start_time;
    },
    minEndTime() {
      return this.params.start_time;
    },
    maxStartTime() {
      return this.params.end_time;
    },
    maxEndTime() {
      return this.timeLimits.end_time || this.now;
    },
    filteredChartData() {
      return this.chartData.filter(chart => this.enabledCharts[chart.id]);
    },
    enableTime() {
      return this.params.timespan !== 'day';
    },
    colors() {
      return this.filteredChartData.map(chart => this.getColor(chart.color));
    },
    data() {
      return this.filteredChartData.map(chart => chart.values.map(statistics => statistics.value));
    },
    tooltips() {
      return this.filteredChartData.map(chart => this.$t(chart.name));
    },
    labels() {
      return this.filteredChartData[0] ? this.filteredChartData[0].values.map(statistics => this.enableTime ? new Date(statistics.time).toLocaleString() : new Date(statistics.time).toLocaleDateString()) : [];
    },
    types() {
      return this.config.types ? this.config.types.map(type => {
        return {
          id: type.id,
          name: this.$t(type.name)
        }
      }) : []
    },
    timespans() {
      let timespans = this.config.timespans.map(timespan => {
        return {
          value: timespan.id,
          name: this.$t(timespan.name)
        }
      })
      if (this.selectedTypeConfig.timespans) {
        timespans = timespans.filter(timespan => this.selectedTypeConfig.timespans.includes(timespan.value));
      }
      return timespans;
    },
    selectedTypeConfig() {
      return this.config.types.find(type => type.id === this.type).config || {};
    }
  },
  async mounted() {
    await this.loadConfig();
    await this.load(true);
  },
  watch: {
    async entityType() {
      await this.loadConfig();
      await this.load(true);
    },
    entityId() {
      this.load(true);
    },
    type() {
      this.load(true);
    },
    params: {
      handler() {
        this.load();
      },
      deep: true
    }
  },
  methods: {
    async loadConfig() {
      this.loadedConfig = false;
      this.config = await this.$api.get(`statistics/config/${this.entityType}`);
      this.type = this.config.types[0].id;
      this.loadedConfig = true;
    },
    async load(enableCharts = false) {
      this.loading = true;
      const params = new URLSearchParams({
        ...this.params,
        start_time: this.params.start_time.toISOString(),
        end_time: this.params.end_time.toISOString(),
      });
      const statistics = await this.$api.get(`statistics/${this.entityType}/${this.entityId}/${this.type}?${params.toString()}`);

      this.chartType = statistics.chart_type;
      this.chartData = statistics.chart_data;
      this.chartNumbers = statistics.numbers || [];

      if (enableCharts) {
        this.enabledCharts = {};
        this.chartData.forEach(chart => {
          this.$set(this.enabledCharts, chart.id, true);
        })
      }
      this.loading = false;
    },
    getColor(color = '--active-color') {
      return color.startsWith('--') ? getComputedStyle(document.getElementById('app')).getPropertyValue(`${color}`) : color;
    }
  },
  data() {
    return {
      loadedConfig: false,
      loaded: false,

      now: new Date(),

      config: {},
      type: null,
      loading: true,

      params: this.startParams ? this.startParams : {
        start_time: new Date(new Date().getTime() - 86400 * 7 * 1000),
        end_time: new Date(),
        aggregate: true,
        timespan: 'day'
      },

      chartType: 'line',
      chartData: [],
      chartNumbers: [],

      enabledCharts: {}
    }
  },
  props: {
    entityType: {
      type: String,
      required: true
    },
    entityId: {
      type: Number,
      required: true
    },
    startParams: {
      type: Object,
      required: false
    },
    timeLimits: {
      type: Object,
      required: false
    }
  },
  components: {
    StatisticsTable,
    lineChart,
    doughnutChart
  }
}
</script>
