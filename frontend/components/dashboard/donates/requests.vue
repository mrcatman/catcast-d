<template>
  <div class="full-height">
    <div class="centered" v-if="loadingInitial">
      <c-preloader  />
    </div>
    <c-nothing-found v-else-if="requests.data.length === 0" :title="$t('global.nothing_found')">
      <div slot="buttons">
        <c-button :icon="'note_add'" @click="addNewRequest()">{{$t('dashboard.donates.requests.add_new')}}</c-button>
      </div>
    </c-nothing-found>
    <div v-else class="list-container">
      <c-infinite-scroll :loading="loading" @scroll="loadMore" class="list-container__inner">
        <div class="list-item list-item--not-link">
          <a @click="addNewRequest()" class="list-item__button">
            <span class="list-item__button__icon">
               <i class="material-icons">note_add</i>
             </span>
            <span class="list-item__button__text">
             {{$t('dashboard.donates.requests.add_new')}}
            </span>
          </a>
        </div>
        <div class="list-item list-item--not-link" :key="$index" v-for="(request, $index) in requests.data">
          <div class="list-item__left">
            <div class="list-item__captions">
              <div class="list-item__title">{{request.sum}} {{$t('currency')}}</div>
              <div class="list-item__under-title">{{$t(getWalletName(request.wallet_type))}} {{request.wallet_id}}</div>
            </div>
          </div>
          <div class="list-item__right">
            {{$t('dashboard.donates.requests.statuses.'+request.status_text)}}
          </div>
        </div>
      </c-infinite-scroll>
    </div>
    <c-modal v-model="requestModalVisible" :header="isEditing ? $t('dashboard.donates.requests.edit') : $t('dashboard.donates.requests.add_new')">
      <div slot="main">
        <c-response :data="response"/>
        <div class="row row--centered">
          <div class="col">
            <div class="modal__input-container">
              <c-select :options="walletsTypes" :errors="errors.wallet_type" :placeholder="$t('dashboard.donates.requests.wallet_type')" v-model="request.wallet_type" />
            </div>
          </div>
          <div class="col">
            <div class="modal__input-container">
              <c-input :errors="errors.wallet_id" :placeholder="$t('dashboard.donates.requests.wallet_id')" v-model="request.wallet_id" />
            </div>
          </div>
        </div>
        <div class="modal__input-container">
          <c-input type="number" :min="1" :max="maxSum" :errors="errors.sum" :placeholder="$t('dashboard.donates.requests.sum')" v-model="request.sum" />
        </div>
        <div class="dashboard__donates__requests__max-sum">
          {{$t('dashboard.donates.requests.max_sum')}} {{maxSum}} {{$t('currency')}}
        </div>
        <div class="modal__input-container">
          <c-input type="textarea" :errors="errors.comment" :placeholder="$t('dashboard.donates.requests.comment')" v-model="request.comment" />
        </div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button @click="saveRequest()" :loading="saving">{{$t('global.ok')}}</c-button>
          <c-button color="red" @click="requestModalVisible = false;">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>
    <c-modal v-model="deleteRequest.modalVisible" :header="$t('dashboard.donates.requests.delete')">
      <div slot="main">
        {{$t('dashboard.donates.requests.delete_confirm')}}
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button @click="deleteSelectedRequest()" :loading="deleteRequest.loading">{{$t('global.ok')}}</c-button>
          <c-button color="red" @click="deleteRequest.modalVisible = false;">{{$t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>
  </div>
</template>
<script>
  let count = 10;
  export default {
    components: {
    },
    async mounted() {
      this.requests = this.requestsList;
      this.maxSum = (await this.$api.get(`donates/getmaxsum/${this.channel.id}`)).data.max_sum;
      this.wallets = (await this.$api.get(`donates/wallets`)).data.wallets;
      this.loadingInitial = false;
    },
    computed: {
      walletsTypes() {
        return this.wallets.map(wallet => {
          return {
            name: this.$t(wallet.display_name),
            value: wallet.name
          }
        })
      }
    },
    data() {
      return {
        maxSum: 0,
        wallets: [],
        currentPage: 1,
        loading: false,
        loadingInitial: true,
        requests: {},
        isEditing: false,
        saving: false,
        editingID: null,
        requestModalVisible: false,
        request: {
          sum: this.maxSum,
        },
        errors: {

        },
        response: {
          status: -1,
          text: ''
        },
        deleteRequest: {
          modalVisible: false,
          id: null,
          loading: false,
        }
      }
    },
    props: {
      requestsList: {
        type: [Object, Array],
        required: false,
      },
      channel: {
        type: [Object],
        required: true
      }
    },
    methods: {
      getWalletName(name) {
        return this.wallets.filter(wallet => wallet.name === name)[0].display_name;
      },
      deleteSelectedRequest() {
        this.deleteRequest.loading = true;
        this.$axios.delete('donates/requests/' + this.deleteRequest.id).then(res=>{
          this.deleteRequest.loading = false;
          this.$store.commit('NEW_ALERT', res.data);
          if (res.data.status) {
            this.deleteRequest.modalVisible = false;
            this.requests.data = this.requests.data.filter(request => request.id !== this.deleteRequest.id);
          }
        })
      },
      saveRequest() {
        let data = this.request;
        data.channel_id = this.channel.id;
        this.saving = true;
        this.$axios({
          method: this.isEditing ? 'PATCH' : 'POST',
          url: this.isEditing ? 'donates/requests/' + this.editingID : 'donates/requests',
          data: data,
        }).then(res => {
          this.saving = false;
          this.errors = res.data.errors || {};
          this.response = res.data;
          if (res.data.status) {
            setTimeout(()=>{
              this.requestModalVisible = false;
            }, 3500)
            if (res.data.request.is_active) {
              this.requests.data.forEach((request, index) => {
                this.requests.data[index].is_active = request.id === res.data.request.id;
              })
            }
            if (!this.isEditing) {
              this.requests.data = [res.data.request, ...this.requests.data];
            } else {
              this.requests.data.forEach((request, index) => {
                if (request.id === res.data.request.id) {
                  this.$set(this.requests.data, index, res.data.request);
                }
              })
            }
          }
        })
      },
      editRequest(request) {
        this.response = null;
        this.isEditing = true;
        this.request = JSON.parse(JSON.stringify(request));
        this.editingID = request.id;
        this.requestModalVisible = true;
      },
      addNewRequest() {
        this.response = null;
        this.request = {
          sum: this.maxSum,
        };
        this.isEditing = false;
        this.requestModalVisible = true;
      },
      loadMore() {
        if (!this.loading) {
          if (this.currentPage < this.requests.last_page) {
            this.currentPage++;
            this.loading = true;
            this.$axios.get(`donates/requests/getbychannel/${this.channel.id}?count=${count}&page=${this.currentPage}`).then(res => {
              this.requests.data = [...this.requests.data, ...res.data.list.data];
              this.loading = false;
            })
          }
        }
      }
    }
  }
</script>

