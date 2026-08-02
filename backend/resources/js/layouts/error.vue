<template>
    <c-error-page v-if="showError" :data="errorData" />
</template>
<style lang="scss">
  .error-page__outer {
    height: 100%;
  }
</style>
<script>


export default {
  mounted() {
    if (this.error._error_status === 401) { // todo: logout
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
  props: {
    error: Object
  }
}
</script>
