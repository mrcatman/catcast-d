<template>
  <auth-form class="register">
    <template slot="main">
      <div class="register__form">
        <transition name="fade" mode="out-in">
          <div v-if="!acceptedRules">
            <c-long-text :text="instanceRules" />
            <c-button big @click="acceptedRules = true">{{$t('global.next')}}</c-button>
          </div>
          <c-form v-else-if="!success" button-class="button--big"
                  :button-text="registrationManual ? $t('auth.register_action_manual') : $t('auth.register_action')"
                  url="/auth/register" @success="registerSuccess">
            <c-input v-form-input="'email'" v-form-validate="'required'" :title="$t('auth.email')"/>
            <c-input v-form-input="'username'" v-form-validate="'required'" :append="`@${siteDomain}`"
                     :title="$t('auth.username')"/>
            <div class="vertical-delimiter"></div>
            <c-input v-form-input="'password'" v-form-validate="'required'" type="password"
                     :title="$t('auth.password')"/>
            <c-input v-form-input="'password_confirmation'" v-form-validate="'required'" type="password"
                     :title="$t('auth.password_confirmation')"/>
            <div v-if="registrationManual">
              <div class="vertical-delimiter"></div>
              <c-input v-form-input="'request_comment'" v-form-validate="'required'" type="textarea"
                       :title="$t('auth.registration_request_comment')" :description="$t('auth.registration_manual')"/>
            </div>
          </c-form>
          <div class="register__success" v-else>
            {{ $t('auth.registration_success') }}
          </div>
        </transition>
      </div>

    </template>
    <template slot="links">
      <router-link v-if="!success" target="_blank" to="/auth/privacy-policy">{{ $t('auth.privacy_policy') }}
      </router-link>
      <router-link v-if="!success" to="/auth/login">{{ $t('auth.login') }}</router-link>
      <router-link v-if="success" to="/auth/resend-confirmation">{{ $t('auth.resend_confirmation.link') }}</router-link>
    </template>
  </auth-form>

</template>
<style lang="scss" scoped>
.register {
  &__form {
    max-width: 30em;
    ::v-deep .long-text {
      margin-bottom: 1em;
    }
  }

  &__success {
    font-weight: 600;
    font-size: 1.125em;
    line-height: 1.4;
    text-align: center;
    margin: 0 auto;
  }
}
</style>
<script>
import {mapGetters, mapState} from "vuex";
import AuthForm from "@/components/auth/AuthForm.vue";
import LayoutWithLeftMenu from "@/components/LayoutWithLeftMenu.vue";

export default {
  components: {LayoutWithLeftMenu, AuthForm},
  middleware({store, redirect}) {
    if (store.state.auth.loggedIn) {
      return redirect('/');
    }
    if (!store.getters['config/registrationEnabled']) {
      return redirect('/login');
    }
  },
  data() {
    return {
      success: false,
      acceptedRules: false
    }
  },
  head() {
    return {
      title: this.$t('auth.register')
    }
  },
  computed: {
    ...mapGetters('config', ['siteDomain', 'instanceRules', 'registrationManual']),

  },
  methods: {
    registerSuccess() {
      this.success = true;
    },
  }
}
</script>
