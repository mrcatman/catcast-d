<template>
    <div class="donates-form">
      <c-response :data="response"/>
      <div class="centered" v-if="loadingInitial">
        <c-preloader  />
      </div>
      <div v-else class="donates-form__content">
        <div class="donates-form__header" v-if="showHeader">
          <span v-if="data.current_goal && data.current_goal.button_text && data.current_goal.button_text.length > 0">{{data.current_goal.button_text}}</span>
          <span v-else-if="data.donates_settings.button_text && data.donates_settings.button_text.length > 0">{{data.settings.button_text}}</span>
          <span v-else>{{$t('donates.default_button_text')}}</span>
       </div>
        <div class="modal__input-container">
          <c-select :errors="errors.provider" v-model="donate.provider" :options="providersList" :placeholder="$t('donates.provider')"/>
        </div>
        <div class="modal__input-container" v-if="!userData.loggedIn">
          <c-input :errors="errors.user_guest_name" v-model="donate.user_guest_name" :placeholder="$t('donates.user_guest_name')"/>
        </div>
        <div class="modal__input-container" v-if="!user.loggedIn">
          <c-input :errors="errors.user_email" v-model="donate.user_email" :placeholder="$t('donates.user_email')"/>
        </div>
        <div class="modal__input-container">
          <c-input :warnings="warnings.sum" :append="$t('currency')" :errors="errors.sum" type="number" :min="1" v-model="donate.sum" :placeholder="$t('donates.sum')"/>
        </div>
        <span class="donates-form__minimal-sum">{{$t('donates.minimal_sum')}} {{data.donates_settings.minimal_sum}} {{$t('currency')}}</span>
        <div class="modal__input-container">
          <c-input :errors="errors.comment" type="textarea" v-model="donate.comment" :placeholder="$t('donates.comment')"/>
        </div>

        <div class="donates-form__warning" v-if="donate.provider === 'account' && donate.sum > balance">
          <i class="material-icons">warning</i>
          <span class="donates-form__warning__text">{{$t('donates.insufficient_balance')}}</span>
          <c-button to="/user/settings#paid">{{$t('donates.balance_top_up')}}</c-button>
        </div>
        <div class="modal__buttons" slot="buttons">
          <div class="buttons-row">
            <c-button v-if="donate.sum > 0" :disabled="sumLessThanMinimal" @click="goToDonate()" :loading="loading">{{$t('donates.go_to_payment')}} <strong>{{realSum}}</strong> {{$t('currency')}}</c-button>
         </div>
        </div>
      </div>
    </div>
</template>
<style lang="scss">
  .donates-form {
    &__header {
      margin: 0 0 1.75em;
      font-size: 1.25em;
      font-weight: 600;
    }
    &__content {
      margin: 1.75em 0 0;
    }
    &__warning {
      white-space: nowrap;
      color: var(--negative-color);
      display: flex;
      align-items: center;
      font-size: 1.1em;
      margin: .5em 0;
      &__text {
        margin: .25em 1em;
      }
    }
  }
  .donates-modal {
    .modal__box {
      padding: 1.25em 1.25em 0;
    }
    .modal__content {
      overflow: visible;
    }
  }
</style>
<script>
  export default {
    async mounted() {
      this.providers = (await this.$api.get('billing/providers')).list;
      this.settings = (await this.$api.get('donates/settings')).data;
      this.balance = (await this.$api.get('billing/balance')).balance;
      this.loadingInitial = false;
    },
    methods: {
      goToDonate() {
        if (!this.sumLessThanMinimal ) {
          this.loading = true;
          let data = this.donate;
          data.channel_id = this.channel.id;
          this.$axios.post('/donates', data).then(res => {
            this.loading = false;
            this.errors = res.data.errors || {};
            this.response = res.data;
            if (res.data.status) {
              if (res.data.link) {
                window.location.replace(res.data.link);
              } else {
                this.$emit('success');
              }
              setTimeout(() => {
                this.$emit('close');
              }, 3500)
            }
          })
        }
      }
    },
    computed: {
      realSum() {
        return (parseInt(this.donate.sum) / 100 * parseInt(this.settings.commission_percent)) + parseInt(this.donate.sum);
      },
      providersList() {
        let data = this.providers.map(provider=>{
          return {
            name: this.$t(provider.display_name),
            value: provider.name
          }
        });
        if (this.userData.loggedIn) {
          data.unshift({
            name: this.$t('donates.withdraw_from_account'),
            value: 'account'
          });
        }
        return data;
      }
    },
    data() {
      return {
        userData: this.$store.state.auth.user,
        loadingInitial: true,
        providers: [],
        settings: {},
        balance: null,
        errors: {},
        warnings: {},
        donate: {
          sum: this.data.donates_settings.minimal_sum,
        },
        sumLessThanMinimal: false,
        response: null,
        loading: false,
      }
    },
    watch: {
      "donate.sum"(sum) {
        let minimal_sum = this.data.donates_settings.minimal_sum;
        if (parseInt(sum) < parseInt(minimal_sum)) {
          this.sumLessThanMinimal = true;
          this.$set(this.warnings, 'sum', [
            'donates.sum_less_than_minimal'
          ])
        } else {
          this.sumLessThanMinimal = false;
          this.$delete(this.warnings, 'sum')
        }
      }
    },
    props: {
      showHeader: {
        type: [Boolean],
        required: false,
      },
      channel: {
        type: [Object],
        required: true,
      },
      data: {
        type: [Object],
        required: true,
      }
    }
  }
</script>
