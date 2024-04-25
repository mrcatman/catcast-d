<template>
  <auth-form>
    <template slot="main">
      <c-form button-class="button--block" :button-text="$t('global.save')" url="/auth/restore" @success="restoreSuccess" :initial-values="initialValues">
        <c-input v-form-input="'password'" type="password" :title="$t('auth.new_password')" />
        <c-input v-form-input="'password_confirmation'" type="password" :title="$t('auth.password_confirmation')" />
      </c-form>
    </template>

  </auth-form>
</template>

<script>
  import AuthForm from "@/components/auth/AuthForm.vue";
  export default {
    middleware: 'not-auth',
    components: {AuthForm},
     head() {
       return {
         title: this.$t('auth.restore_account')
       }
     },
     data() {
      return {
        initialValues: {
          code: this.$route.query.code,
        },
        success: false,
      }
    },
    mounted() {
       if (!this.$route.query.code) {
        this.$router.redirect('/');
      }
    },
    methods: {
      restoreSuccess(user) {
        this.$store.commit('auth/setUser', user);
        setTimeout(()=>{
          this.$router.push(`/users/${user.id}`);
        },2500)
      }
    }
  }
</script>
