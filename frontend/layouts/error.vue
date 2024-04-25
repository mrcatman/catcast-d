<template>
  <c-error-page :data="errorData" v-if="showError" />
</template>
<style lang="scss">
  .error-page__outer {
    height: 100%;
  }
</style>
<script>
export default {
  mounted() {
    if (this.error._error_status === 401) {
      return this.$router.replace('/auth/login');
    } else if (this.error._error_status === 403) {
      return this.$router.replace('/');
    } else {
      this.showError = true;
    }
  },
  data() {
    return {
      showError: false
    }
  },
  computed: {
    errorData() {
      return {
        code: this.error._error_status,
        title: this.error._error_status || 500,
        text: this.$t(this.error.message),
      }
    }
  },
  layout: 'empty',
  props: {
    error: Object
  }
}
</script>
