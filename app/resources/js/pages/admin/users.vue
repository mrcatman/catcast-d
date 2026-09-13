<template>
  <div class="box box--with-header">
    <c-preloader block  v-if="loading" />
    <div class="box__header box__header--dark">
      <div class="row">
        <div class="col">
          <c-input @change="onNameChange()" v-model="name" :title="$t('admin.users.username')"/>
        </div>
      </div>
    </div>
    <div class="box__inner admin-panel__content">
      <table class="table">
        <thead>
          <tr>
            <td class="table__sortable-field" @click="setSort('id')">{{$t('admin.users.id')}}</td>
            <td>{{$t('admin.users.username')}}</td>
            <td class="table__sortable-field" @click="setSort('lastseen')">{{$t('admin.users.last_seen')}}</td>
            <td>{{$t('admin.users.balance')}}</td>
            <td>{{$t('admin.users.channels')}}</td>
            <td></td>
          </tr>
        </thead>
        <tbody>
           <tr :key="$index" v-for="(user, $index) in users.data">
            <td><nuxt-link target="_blank" :to="`/users/${user.id}`">{{user.id}} </nuxt-link><nuxt-link target="_blank" :to="`/mail?user_id=${user.id}`">({{$t('admin.users.mail')}})</nuxt-link></td>
            <td>{{user.username}}</td>
            <td>{{formatPublishDate(user.last_seen)}}</td>
            <td>{{user.balance}}</td>
            <td>{{user.channels_names}}</td>
            <td>
              <c-button @click="topUpBalance(user)">{{$t('admin.users.top_up_balance')}}</c-button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="box__footer">
      <c-pager :data="users" v-model="currentPage" />
    </div>

    <c-modal v-model="balancePanel.visible">
      <div slot="main">
        <div class="modal__input-container">
          <c-input :title="$t('admin.users.balance_sum')" v-model="balancePanel.data.sum"/>
        </div>
      </div>
      <div slot="buttons">
        <div class="buttons-row">
          <c-button @click="applyBalanceTopUp()" :loading="balancePanel.loading">{{$t('global.ok')}}</c-button>
          <c-button flat @click="balancePanel.visible = false">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>

  </div>
</template>
<script>
  const count = 12;
  import { formatPublishDate } from '@/helpers/dates.js';
  export default {
    methods: {
      formatPublishDate,
      setSort(field) {
        if (this.sort === field) {
          this.sortOrder = this.sortOrder === "ASC" ? "DESC" : "ASC";
        } else {
          this.sort = field;
        }
        this.load();
      },
      applyBalanceTopUp() {
        this.balancePanel.loading = true;
        this.$axios.post(`admin/users/balance`, {sum: this.balancePanel.data.sum, user_id: this.balancePanel.user.id}).then(res => {
          this.balancePanel.loading = false;
          this.$store.commit('NEW_ALERT', res.data);
          if (res.data.status) {
            this.balancePanel.user.balance = res.data.data.new_balance;
            this.balancePanel.visible = false;
          }
        })
      },
      topUpBalance(user) {
        this.balancePanel.user = user;
        this.balancePanel.visible = true;
      },
      onNameChange() {
        if (this.currentPage !== 0) {
          this.currentPage = 0;
        } else {
          this.load();
        }
      },
      async load() {
        this.loading = true;
        let data = (await this.$api.get(`admin/users?page=${this.currentPage}&sort=${this.sort}&sort_order=${this.sortOrder}&count=${count}&name=${this.name}`));
        if (!data.status) {
          this.$store.commit('NEW_ALERT', data);
        } else {
          this.users = data.data.users;
        }
        this.loading = false;
      },
    },
    watch: {
      currentPage(newPage) {
        this.load();
      }
    },
    async mounted() {
      this.load();
    },
    data() {
      return {
        sort: 'id',
        sortOrder: 1,
        balancePanel: {
          loading: false,
          user: null,
          visible: false,
          data: {
            sum: 0
          }
        },
        loading: true,
        currentPage: 1,
        name: '',
        users: {
          data: []
        }
      }
    }
  }
</script>
