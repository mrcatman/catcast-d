<template>
  <div>
    <div class="centered-block">
      <div class="box box--with-header admin-panel__donates">
        <div class="box__header">
          <div class="box__header__title">
            {{$t('admin.donate_requests.heading')}}
          </div>
        </div>
        <div class="box__inner">
          <div class="list-container" >
            <div class="list-container__inner">
              <c-nothing-found v-if="!loading && requests.total === 0" />
              <a class="list-item list-item--without-picture" :key="request.id" v-for="request in requests.data">
                <div class="list-item__left">
                  <div class="list-item__captions">
                    <div class="list-item__title">{{$t(getWalletName(request.wallet_type))}} ({{request.wallet_id}})</div>
                    <div>{{$t('admin.donate_requests.sum')}} {{request.sum}} {{$t('currency')}}</div>
                    <div class="list-item__sub">{{$t('admin.donate_requests.user')}} {{request.user ? request.user.username : ''}}, {{$t('admin.donate_requests.user_comment')}} {{request.comment}}</div>
                  </div>
                </div>
                <div class="list-item__right">
                  <div class="admin-panel__donates__status">{{$t('dashboard.donates.requests.statuses.'+request.status_text)}}</div>
                  <div class="buttons-row">
                    <c-button @click="approve(request)">{{$t('admin.donate_requests.approve')}}</c-button>
                    <c-button color="red" @click="decline(request)">{{$t('admin.donate_requests.decline')}}</c-button>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
        <div class="box__footer" v-if="requests.last_page > 1">
          <c-pager :data="donates" v-model="currentPage" />
        </div>
      </div>
    </div>

    <c-modal v-model="declinePanel.visible">
      <div slot="main">
        <div class="modal__input-container">
          <c-input type="textarea" :placeholder="$t('admin.donate_requests.admin_comment')" v-model="declinePanel.data.admin_comment" />
        </div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button @click="declineSelectedRequest()" :loading="declinePanel.loading">{{$t('global.ok')}}</c-button>
          <c-button color="red" @click="declinePanel.visible = false;">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>

  </div>
</template>
<style lang="scss">
  .admin-panel {
    &__donates {
      width: 70vw;
      &__status {
        margin: 0 1em 0 0;
      }
    }
  }
</style>
<script>
  import { formatPublishDate, formatFullDate } from '@/helpers/dates.js';
  export default {
    computed: {
      walletsTypes() {
        return this.wallets.map(wallet => {
          return {
            name: this.$t(wallet.display_name),
            value: wallet.name
          }
        })
      },
      categoriesList() {
        return this.categories.map(category => {
          return {
            name: this.$t(category.text),
            value: category.id
          }
        })
      }
    },
    methods: {
      declineSelectedRequest() {
        this.declinePanel.loading = true;
        this.$axios.post(`admin/donaterequests`, {status: 0, admin_comment: this.declinePanel.data.admin_comment, request_id: this.declinePanel.request.id}).then(res => {
          this.declinePanel.loading = false;
          this.$store.commit('NEW_ALERT', res.data);
          this.declinePanel.data.status_text = res.data.data.request.status_text;
          if (res.data.status) {
            this.declinePanel.visible = false;
          }
        })
      },
      decline(request) {
        this.declinePanel.request = request;
        this.declinePanel.visible = true;
      },
      approve(request) {
        this.$axios.post(`admin/donaterequests`, {status: 1, request_id: request.id}).then(res => {
          this.$store.commit('NEW_ALERT', res.data);
          request.status_text = res.data.data.request.status_text;
        })
      },
      getWalletName(name) {
        return this.wallets.filter(wallet => wallet.name === name)[0].display_name;
      },
      async load() {
        this.loading = true;
        this.requests = (await this.$api.get(`admin/donaterequests?page=${this.currentPage}`)).data.requests;
        this.loading = false;
      },
      formatFullDate,
      formatPublishDate,
    },
    async mounted() {
      this.loading = true;
      let data = (await this.$api.get(`admin/donaterequests`));
      this.wallets = (await this.$api.get(`donates/wallets`)).data.wallets;
      if (data.status) {
        this.requests = data.data.requests;
     } else {
        this.$store.commit('NEW_ALERT', data);
      }
      this.loading= false;
    },
    data() {
      return {
        requests: {},
        wallets: [],
        declinePanel: {
          data: {
            admin_comment: '',
          },
          loading: false,
          request: null,
          visible: false,
        },
        currentPage: 1,
        loading: false
      }
    },
    watch: {
      currentPage(newPage) {
        this.load();
      }
    },
    head () {
      return {
        title: this.$t('admin.donate_requests.heading'),
      }
    },
  }
</script>
