<template>
  <div class="box box--with-header">
    <c-preloader block  v-if="loading" />
    <div class="box__header box__header--dark">
      <div class="row">
        <div class="col">

        </div>
      </div>
    </div>
    <div class="box__inner admin-panel__content">
      Видеосерверы:
      <div :key="serverUrl" v-for="(server, serverUrl) in servers.video_servers">
        <strong>{{serverUrl}}</strong>
        {{server.statistics}}
      </div>
      <br>
      Радиосерверы:
      <div :key="server.ip_address" v-for="(server) in servers.radio_servers">
        <strong>{{server.ip_address}}</strong>
        {{server.statistics}}
      </div>
    </div>
  </div>
</template>
<script>
  import axios from 'axios';
  export default {
    methods: {
      getRadioStatsText(stats) {
        let data = stats.icestats.source;
        if (!Array.isArray(data)) {
          data = [data];
        }
        let total = 0;
        let channelsCount = Object.keys(data).length;
        data.forEach(channel => {
          total+=channel.listeners;
        })
        return `Слушателей: ${total}, радиостанций вещает: ${channelsCount}`;
      },
      getStatsText(stats) {
        let total = 0;
        let channelsCount = Object.keys(stats.data).length;
        Object.keys(stats.data).forEach(channel => {
          total+=stats.data[channel].count;
        })
        return `Зрителей: ${total}, каналов смотрят: ${channelsCount}`;
      },
      async check() {
        for (let server in this.servers.video_servers) {
          let serverData = this.servers.video_servers[server];
          try {
            this.$set(this.servers.video_servers[server], 'statistics', "Загрузка...");
            let statistics = (await axios.get(serverData.statistics_url)).data;
            let statisticsText = this.getStatsText(statistics);
            this.$set(this.servers.video_servers[server], 'statistics', statisticsText);
          } catch (e) {
            console.log(e);
            this.$set(this.servers.video_servers[server], 'statistics', "Ошибка получения статистики");
          }
        }
        for (let server in this.servers.radio_servers) {
          try {
            this.$set(this.servers.radio_servers[server], 'statistics', "Загрузка...");
            let statistics = (await axios.get("http://"+this.servers.radio_servers[server].ip_address+":8000/status-json.xsl")).data;
            let statisticsText = this.getRadioStatsText(statistics);
            this.$set(this.servers.radio_servers[server], 'statistics', statisticsText);
          } catch (e) {
            console.log(e);
            this.$set(this.servers.radio_servers[server], 'statistics', "Ошибка получения статистики");
          }
        }
      },
      async load() {
        this.loading = true;
        let data = (await this.$api.get(`admin/servers`));
        if (!data.status) {
          this.$store.commit('NEW_ALERT', data);
        } else {
          this.servers = data.data;
          this.check();
        }
        this.loading = false;

      },
    },
    watch: {
    },
    async mounted() {
      this.load();
    },
    data() {
      return {
        loading: false,
        servers: {}
      }
    }
  }
</script>
