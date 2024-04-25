<template>
  <div class="billing-page">
    <div class="centered" v-if="loading">
      <c-preloader  />
    </div>
    <div v-else>
      <div v-if="!refillShown">
        <div class="billing-page__balance-container">
          <div class="billing-page__balance__texts">
            <span class="billing-page__balance-description">{{$t('billing.your_balance')}}</span>
            <span class="billing-page__balance-number">{{balance}}</span>
            <span class="billing-page__balance-currency">{{$t('billing.currency')}}</span>
          </div>
          <div class="billing-page__balance__buttons">
            <c-button @click="refillShown = true">{{$t('billing.recharge')}}</c-button>
          </div>
        </div>
        <div class="settings-page__header settings-page__header--small">{{$t('billing.history.heading')}}</div>
        <div class="list-container">
          <c-tabs small :data="billingHistoryTabs" v-model="currentBillingHistoryTab" />
          <div class="list-container__inner billing-page__history">
            <div class="list-item list-item--small" :key="$index" v-for="(item,$index) in billingHistorySorted()">
              <div class="list-item__left">
                <div class="list-item__captions">
                  <div class="list-item__title" v-if="item.type === 'expenses'">{{$t(item.expense_type_name)}} <span class="tag tag--flat">{{formatPublishDate(item.created_at, false)}}</span></div>
                  <div class="list-item__title" v-if="item.type === 'recharges'">{{$t(item.recharge_type_name)}} <span class="tag tag--flat">{{formatPublishDate(item.created_at, false)}}</span></div>
                  <div class="list-item__under-title">
                    <c-tag  v-if="item.type === 'expenses'">{{$t('billing.history.expenses')}}</c-tag>
                    <c-tag  v-else-if="item.type === 'recharges'">{{$t('billing.history.recharges')}}</c-tag>
                    <span class="tag tag--red" v-if="item.type === 'expenses'">{{item.sum}} {{$t('billing.currency')}}</span>
                    <span class="tag tag--green" v-else-if="item.type === 'recharges'">{{item.sum}} {{$t('billing.currency')}}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else>
        <div class="row row--centered">
          <div class="col">
            <c-select v-model="recharge.provider" :options="providersList" :placeholder="$t('billing.select_recharge_provider')"/>
          </div>
          <div class="col">
            <c-input :append="$t('billing.currency')" type="number" v-model="recharge.sum" :placeholder="$t('billing.sum')"/>
          </div>
        </div>
        <div class="buttons-row">
          <c-button @click="goToRecharge()">{{$t('billing.go_to_recharge')}}</c-button>
          <c-button flat @click="refillShown = false">{{$t('billing.go_back')}}</c-button>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .billing-page {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    .list-container {
      max-height: 50vh;
      @media screen and (max-width: 768px) {
        max-height: unset;
        font-size: 1em;
      }
    }
    &__balance {
      &-container {
        display: flex;
        justify-content: space-between;
      }
      &__texts {
        display: flex;
        align-items: center;
      }
      &-number {
        font-size: 1.75em;
        margin: 0 .5em;
        font-weight: 600;
      }
    }
  }
</style>
<script>
import { formatPublishDate } from '@/helpers/dates.js';
export default {
  async mounted() {
    this.loading = true;
    this.balance = (await this.$api.get('billing/balance')).balance;
    this.providers = (await this.$api.get('billing/providers')).list;
    this.recharge.provider = this.providers[0].name;
    this.history = (await this.$api.get('billing/history')).list;
    this.loading = false;
  },
  computed: {

    billingHistory() {
      if (!this.history) {
        return [];
      }
      if (!this.history.expenses || !this.history.recharges) {
        return [];
      }
      let tab = this.currentBillingHistoryTab;
      let history = this.history;
      if (tab === 'all') {
        return [
          ...history.expenses.map(item=>{
            item.type = 'expenses';
            return item;
          }),
          ...history.recharges.map(item=>{
            item.type = 'recharges';
            return item;
          })
        ];
      }
      if (tab === 'expenses') {
        return history.expenses.map(item=>{
          item.type = 'expenses';
          return item;
        });
      }
      if (tab === 'recharges') {
        return history.recharges.map(item=>{
          item.type = 'recharges';
          return item;
        });
      }
    },
    billingHistoryTabs() {
      return [
        {id: 'all', name: this.$t('billing.history.all')},
        {id: 'expenses', name: this.$t('billing.history.expenses')},
        {id: 'recharges', name: this.$t('billing.history.recharges')},
      ];
    },
    providersList() {
      return this.providers.map(provider=>{
        return {
          name: this.$t(provider.display_name),
          value: provider.name
        }
      });
    }
  },
  methods: {
    billingHistorySorted() {
      return this.billingHistory.sort(function(a,b){
        return new Date(b.created_at) - new Date(a.created_at);
      });
    },
    formatPublishDate,
     goToRecharge() {
        let link = this.$store.state.apiURL+"billing/recharge?sum="+(this.recharge.sum)+"&token="+this.$store.state.token+"&provider="+this.recharge.provider;
        window.open(link);
     }
  },
  data() {
    return {
      refillShown: false,
      loading: false,
      balance: 0,
      providers: [],
      history: null,
      currentBillingHistoryTab: 'all',
      recharge: {
        provider: null,
        sum: 100,
      },
    }
  }
}
</script>
