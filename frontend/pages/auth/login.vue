<template>
<auth-form>
  <template slot="main">
    <c-form button-class="button--big" :button-text="$t('auth.login_action')"  url="/auth/login" @success="loginSuccess" :success-message="$t('auth.login_success')">
      <c-input v-form-input="'username'" v-form-validate="'required'" :title="$t('auth.username')" />
      <c-input v-form-input="'password'" v-form-validate="'required'" type="password" :title="$t('auth.password')" />
    </c-form>
  </template>
  <template slot="links">
    <router-link to="/auth/forgot-password" >{{$t('auth.forgot_password.link')}}</router-link>
    <router-link to="/auth/register" v-if="registrationEnabled">{{$t('auth.register')}}</router-link>
  </template>
</auth-form>
</template>
<script>
import AuthForm from "@/components/auth/AuthForm.vue";
import {mapGetters, mapState} from "vuex";

export default {
  middleware: 'not-auth',
  components: {
    AuthForm
  },
  computed: {
    ...mapState('auth', ['loggedIn']),
    ...mapGetters('config', ['registrationEnabled'])
  },
  head() {
    return {
      title: this.$t('auth.login')
    }
  },
  beforeDestroy() {
    window.removeEventListener('storage', this.onStorage);
  },
  mounted() {
    window.addEventListener('storage', this.onStorage);
  },
	methods: {
    onStorage(e) {
      if (e.key === "auth_event") {
        window.close();
      }
    },
		async loginSuccess(user) {
      this.$store.commit('auth/setUser', user);
      if (this.$route.query && this.$route.query.return) {
        this.$router.push(this.$route.query.return);
      } else {
        this.$router.push(`/users/${user.id}`);
      }
    }
	}
}
</script>
