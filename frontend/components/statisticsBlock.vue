<template>
  <div class="statistics">
    <c-preloader v-if="!loadedConfig" />
    <div v-else>
      <div class="statistics__top">
        <c-row align="end">
          <c-col auto-width>
            <c-tabs v-model="params.timespan" :data="timespans"/>
          </c-col>
          <c-col auto-width>
            <c-datetime-picker :max-date="today" :enable-time="byHour" :title="$t('statistics.time.start')" v-model="params.start_time"/>
          </c-col>
          <c-col auto-width>
            <c-datetime-picker :max-date="today" :enable-time="byHour" :title="$t('statistics.time.end')" v-model="params.end_time"/>
          </c-col>
          <c-col auto-width>
            <c-checkbox :title="$t('statistics.aggregate')" v-model="params.aggregate"/>
          </c-col>
        </c-row>
      </div>


      <c-tabs v-model="type" :data="types"/>

      <div class="statistics__chart-container">
        <c-preloader block v-show="loading" />
        <line-chart class="chart statistics__chart" v-if="chartType === 'line'" :colors="colors" :tooltips="tooltips" :labels="labels" :data="data" :display-legend="false" />
        <table v-else-if="chartType === 'table'" class="table" v-for="table in chartData">
          <thead>
          <tr>
            <td :key="$index" v-for="(col, $index) in table.headings">{{$t(col)}}</td>
          </tr>
          </thead>
          <tbody>
          <tr :key="$index" v-for="(row, $index) in table.values">
            <td :key="$index2" v-for="(col, $index2) in row">{{col}}</td>
          </tr>
          </tbody>
        </table>

      </div>

      <c-row justify="center" v-if="chartData.length > 1">
        <c-col auto-width v-for="chart in chartData" :key="chart.id" >
          <c-checkbox v-model="enabledCharts[chart.id]" :title="$t(chart.name)" :color="getColor(chart.color)" />
        </c-col>
      </c-row>


      <div class="statistics__inner">

        <div class="statistics__info-container">
          <div class="statistics-page__chart__data statistics-page__chart__data--horizontal" v-if="type === 'views'">
            <div class="statistics-page__chart__data__item">
              <span class="statistics-page__chart__data__title">{{ $t('statistics.views_by_day.total') }}</span>
              <span class="statistics-page__chart__data__number">test</span>
            </div>
          </div>
        </div>
      </div>
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

  &__inner {
    margin: 0;
    display: flex;
    /*align-items: center;*/
    background: var(--box-element-color);
    padding: 2.5em 1em 1em;
  }



  &__info-container {
    margin: 0 0 0 1em;
    flex: 1;
  }
}
</style>
<script>
import lineChart from '@/components/dashboard/charts/lineChart.js';
import doughnutChart from '@/components/dashboard/charts/doughnutChart.js';

export default {
  computed: {
    filteredchartData() {
      return this.chartData.filter(chart => this.enabledCharts[chart.id]);
    },
    byHour() {
      return this.params.timespan === 'hour';
    },
    colors() {
      return this.filteredchartData.map(chart => this.getColor(chart.color));
    },
    data() {
      return this.filteredchartData.map(chart => chart.values.map(filteredchartData => filteredchartData.value));
    },
    tooltips() {
      return this.filteredchartData.map(chart => this.$t(chart.name));
    },
    labels() {
      return this.filteredchartData[0] ? this.filteredchartData[0].values.map(statistics => this.byHour ? new Date(statistics.time).toLocaleString() : new Date(statistics.time).toLocaleDateString()) : [];
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
      return this.config.timespans ? this.config.timespans.map(timespan => {
        return {
          id: timespan.id,
          name: this.$t(timespan.name)
        }
      }) : []
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
      today: new Date(),
      config: {},
      params: {
        start_time: new Date(new Date().getTime() - 86400 * 7 * 1000),
        end_time: new Date(),
        aggregate: true,
        timespan: 'day'
      },
      type: null,
      loading: true,
      chartType: 'line',
      chartData: [],
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

  },
  components: {
    lineChart,
    doughnutChart
  }
}
</script>
