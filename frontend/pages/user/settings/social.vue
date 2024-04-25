<template>
  <div>
    <div v-if="loading" class="centered">
      <c-preloader  />
    </div>
    <div v-else>
      <div class="social-connections__description">
        {{$t('connections.social.description')}}
      </div>
      <div class="row row--wrap">
        <div :key="$index" v-for="(provider, $index) in socialProviders" class="col social-connections__provider social-connections__provider--with-delimiter">
          <div class="social-connections__provider__info">
            <div class="social-connections__provider__name">
              {{provider.name}}
            </div>
          </div>
          <div class="social-connections__provider__buttons">
            <c-button v-if="!provider.connected_id" :to="getSocialProviderLink(provider)" :color="provider.color" :textcolor="provider.textcolor">{{$t('connections.social.connect')}}</c-button>
            <c-button @click="disconnect(provider)" flat v-else>{{$t('connections.social.disconnect')}}</c-button>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col social-connections__provider">
          <div class="social-connections__provider__info">
            <div class="social-connections__provider__name">{{$t('connections.bots.heading')}}</div>
            <div class="social-connections__provider__description">{{$t('connections.bots.description')}}</div>
          </div>
          <div class="social-connections__provider__buttons">
            <c-button fabIcon="telegram" to="https://vee.gg/t/CatcastBot">{{$t('connections.bots.telegram')}}</c-button>
            <c-button fabIcon="vk" to="https://vk.com/catcast_bot">{{$t('connections.bots.vk')}}</c-button>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col social-connections__provider">
          <div class="social-connections__provider__info">
            <div class="social-connections__provider__name">{{$t('connections.email.heading')}}</div>
            <div class="social-connections__provider__description">{{$t('connections.email.description')}}</div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <c-input :disabled="emailDisabled" v-model="email" :errors="errors.email" :warnings="warnings.email" :placeholder="$t('connections.email.enter_email')" />
        </div>
        <div class="col col--button-container">
          <c-button v-if="emailDisabled"  @click="emailDisabled = false">{{$t('connections.email.change_email')}}</c-button>
          <c-button v-else-if="oldEmail === email"  @click="emailDisabled = true">{{$t('global.ok')}}</c-button>
          <c-button v-else :loading="isSendingConfirmation" @click="sendConfirmation()">{{$t('connections.email.send_confirmation')}}</c-button>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .col.social-connections__provider {
    margin: 1em 0 0!important;
  }
  .social-connections__provider {
    display: flex;
    align-items: center;
    justify-content: space-between;
    &:only-child{
      padding: 0!important;
    }
    &--with-delimiter{
      border-left: 1px solid rgba(255, 255, 255, 0.1);
      padding: 0 1em;
      &:last-child {
        padding: 0 0 0 1em;
      }

    }
    &__name {
      font-weight: 600;
    }
    &__description{
      font-size: .875em;
    }
    &__buttons {
      text-align: right;
    }
  }
</style>
<script>
  export default {
    watch: {
      emailDisabled(isDisabled) {
        if (!isDisabled) {
          this.oldEmail = this.email;
        }
      },
      email(newEmail) {
        const emailRegexp = /\S+@\S+\.\S+/;
        if (newEmail.length === 0 || !emailRegexp.test(newEmail)) {
          this.warnings.email = ['connections.email.enter_correct_email'];
        } else {
          this.warnings.email = [];
        }
      }
    },
    async mounted() {
      let socialProvidersList = (await this.$api.get(`/auth/social/providers`));
      let externalConnections = (await this.$api.get(`/auth/connections/get`));
      this.externalConnections = externalConnections.list;
      externalConnections.list.forEach(connection=>{
        if (connection.account_type === 'email') {
          this.emailSet = true;
          this.emailDisabled = true;
          this.email = connection.account_name;
        }
      });
      this.socialProviders = socialProvidersList;
      this.loading = false;
    },
    methods: {
      disconnect(provider) {
        this.$axios.post('/auth/social/disconnect',{provider: provider.id}).then(res=>{
          this.$store.commit('NEW_ALERT', res.data);
          if (res.data.status) {
            provider.connected_id = null;
          }
        })
      },
      sendConfirmation() {
        const emailRegexp = /\S+@\S+\.\S+/;
        if ((this.warnings.email && this.warnings.email.length > 0) || this.email === '') {

        } else {
          this.isSendingConfirmation = true;
          this.$axios.post('/auth/connections/sendconfirmation',{type: 'email', account_id: this.email}).then(res=>{
            this.isSendingConfirmation = false;
            this.$store.commit('NEW_ALERT', res.data);
            if (!res.data.status) {
              this.errors = res.data.errors || {};
              if (res.data.errors.account_id) {
                this.errors.email = res.data.errors.account_id;
              }
            } else {
              this.emailSet = true;
            }
          })
        }

      },
      getSocialProviderLink(provider) {
        return this.$store.state.apiURL+"auth/social/connect?token="+this.$store.state.token+"&provider="+provider.id;
      }
    },
    data() {
      return {
        email: '',
        emailDisabled: false,
        oldEmail: null,
        emailSet: false,
        loading: true,
        isSendingConfirmation: false,
        socialProviders: [],
        externalConnections: [],
        errors: {},
        warnings: {},
      }
    }
  }
</script>
