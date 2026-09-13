<template>
  <div class="full-height">
    <div class="centered" v-if="loadingInitial">
      <c-preloader  />
    </div>
    <c-nothing-found v-else-if="donates.data.length === 0" :title="$t('global.nothing_found')" />
    <div v-else class="list-container">
      <c-infinite-scroll  :loading="loading" @scroll="loadMore" class="list-container__inner">
        <div class="list-item list-item--not-link" :key="$index" v-for="(donate, $index) in donates.data">
          <div class="list-item__left">
             <div class="list-item__number">
               {{donate.sum_readable}}
             </div>
              <div class="list-item__captions">
                <div v-if="donate.user" class="list-item__title">{{donate.user.username}}</div>
                <div class="list-item__under-title">{{donate.comment}}</div>
              </div>
          </div>
          <div class="list-item__right">
            <div class="dashboard-page__donates__time">{{donate.time_readable}}</div>
          </div>
        </div>
      </c-infinite-scroll>
    </div>
  </div>
</template>
<script>
  let count = 10;
  export default {
    components: {
    },
    async mounted() {
      let donates = (await this.$api.get($api.get(`donates/getlistbychannel/${this.channel.id}?count=${count}`)));
      this.donates = donates.data.list;
      this.loadingInitial = false;
    },
    data() {
      return {
        currentPage: 1,
        loading: false,
        loadingInitial: true,
        donates: {},
      }
    },
    props: {
      channel: {
        type: [Object],
        required: true
      }
    },
    methods: {
      loadMore() {
        if (!this.loading) {
          if (this.currentPage < this.donates.last_page) {
            this.currentPage++;
            this.loading = true;
            this.$axios.get(`donates/getlistbychannel/${this.channel.id}?count=${count}&page=${this.currentPage}`).then(res => {
              this.donates.data = [...this.donates.data, ...res.data.data.list.data];
              this.loading = false;
            })
          }
        }
      }
    }
  }
</script>
