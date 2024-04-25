<template>
  <auth-form>
    <template slot="main">
      <c-preloader v-if="loading"  />
      <div v-else class="confirm">
        <i18n path="auth.confirm.welcome" tag="div" class="confirm__welcome">
          <strong place="server">{{siteName}}</strong>
          <strong place="username">{{user.username}}</strong>
        </i18n>
        <div class="confirm__text" v-if="registrationManual">
          {{$t('auth.confirm.text_registration_manual')}}
        </div>
        <div class="vertical-delimiter"></div>
        <div class="buttons-row confirm__buttons">
          <c-button to="/">{{$t('auth.confirm.show_channels')}}</c-button>
          <c-button v-if="!registrationManual" color="green" to="/dashboard">{{$t('auth.confirm.go_to_dashboard')}}</c-button>
        </div>

      </div>
    </template>
  </auth-form>
</template>
<style lang="scss" scoped>
.confirm {
  max-width: 32em;
  text-align: center;
  &__welcome {
    font-size: 1.625em;
    margin: 1em;
    font-weight: 500;
  }
  &__text {
    font-size: 1.25em;
  }

  &__buttons {
    justify-content: center;
  }
}

</style>
<script>
  import AuthForm from "@/components/auth/AuthForm.vue";
  import {mapGetters} from "vuex";

  export default {
    middleware: 'not-auth',
    components: {AuthForm},
    computed: {
      ...mapGetters('config', ['siteName', 'registrationManual'])
    },
    head() {
      return {
        title: this.$t('auth.confirm_account')
      }
    },
    data() {
      return {
        loading: true,
        user: null
      }
    },
    mounted() {
      this.loading = true;
      if (!this.$route.query.code) {
        this.$router.push('/');
      }
      this.$api.post(`/auth/confirm/${this.$route.query.code}`).then( user => {
        this.user = user;
      }).finally(() => {
        this.loading = false;
      })
    }
  }
</script>
