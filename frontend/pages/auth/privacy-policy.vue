<template>
  <auth-form>
    <template slot="main">
      <c-long-text :text="privacyPolicy" />
    </template>
  </auth-form>
</template>
<script>
import AuthForm from "@/components/auth/AuthForm.vue";
import {mapGetters} from "vuex";
export default {
  components: {
    AuthForm
  },
  computed: {
    ...mapGetters('config', ['privacyPolicy'])
  },
  head() {
    return {
      title: this.$t('auth.privacy_policy')
    }
  },
}
</script>
