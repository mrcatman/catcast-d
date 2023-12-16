<template>
<auth-form>
  <template slot="main">
    <c-form button-class="button--big" :button-text="$t('auth.forgot_password.send')"  url="/auth/forgot-password">
      <c-input v-form-input="'email'" v-form-validate="'email'" :title="$t('auth.forgot_password.email')" />
    </c-form>
  </template>
  <template slot="links">
    <router-link to="/auth/login">{{$t('auth.login')}}</router-link>
  </template>
</auth-form>
</template>
<script>
import AuthForm from "@/components/auth/AuthForm.vue";
export default {
  middleware: 'not-auth',
  components: {
    AuthForm
  },
  head() {
    return {
      title: this.$t('auth.forgot_password._title')
    }
  },
}
</script>
