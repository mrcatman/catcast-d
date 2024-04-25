<template>
  <div class="channel-layout__announces">
    <div class="list-container" v-if="loading || loadingInitial || (timetable.data && timetable.data.length > 0)">
      <c-preloader v-if="loadingInitial" />
      <c-infinite-scroll v-else :loading="loading" @scroll="loadMore" class="list-container__inner">
        <div class="list-item list-item--auto list-item--not-link" :key="$index" v-for="(announce, $index) in timetable.data">
          <div class="list-item__left">
            <div class="list-item__picture-block list-item__picture-block--big" :style="{backgroundImage: 'url('+announce.picture+')'}" ></div>
            <div class="list-item__captions list-item__captions--small">
              <div class="list-item__title">
                {{announce.title}}
              </div>
              <div class="list-item__sub">
                {{announce.description}}
              </div>
            </div>
          </div>
          <div class="list-item__date-container">
            <div class="list-item__date">{{formatFullDate(announce.time, true, false)}}</div>
            <AnnounceSubscribeButton :data="announce"/>
          </div>
        </div>
      </c-infinite-scroll>
    </div>
    <c-nothing-found v-else/>
  </div>
</template>

<script>
  import AnnounceSubscribeButton from '@/components/buttons/AnnounceSubscribeButton';
  import { formatPublishDate, formatFullDate } from '@/helpers/dates.js';
  export default {
    components: {
      AnnounceSubscribeButton
    },
    async mounted() {
      await this.loadTimetable();
      this.loadingInitial = false;
      this.$parent.$on('scrollBottom', () => {
        this.loadMore();
      })
    },
    props: {
      channel: {
        type: [Object],
        required: true,
      }
    },
    watch: {
    },
    data() {
      return {
        loadedAll: false,
        loadingInitial: true,
        loading: false,
        timetable: {},
        currentPage: 1,
      }
    },
    methods: {
      async loadMore() {
        if (!this.loading) {
          if (this.timetable.data.length > 0) {
            if (!this.loadedAll) {
              this.loading = true;
              let time = this.timetable.data[this.timetable.data.length - 1].time + 1;
              let timetable = (await this.$api.get(`timetable/getnextbychannel/${this.channel.id}?time=${time}`));
              let data = timetable.data.timetable.data;
              this.timetable.data = [...this.timetable.data, ...data];
              if (data.length === 0) {
                this.loadedAll = true;
              }
              this.loading = false;

            }
          }
        }
      },
      formatPublishDate,
      formatFullDate,
      async loadTimetable() {
        let timetable = (await this.$api.get(`timetable/getnextbychannel/${this.channel.id}`));
        this.timetable = timetable.data.timetable;
      }
    }
  }
</script>

