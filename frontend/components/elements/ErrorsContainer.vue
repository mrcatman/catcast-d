<template>
  <div class="errors-container">
    <div class="input__errors" v-show="errors && errors.length > 0">
      <div v-for="(error,$index) in errors" :key="$index" class="input__error">{{printError(error)}}</div>
    </div>
    <div class="input__warnings" v-show="warnings && warnings.length > 0 && (!errors || errors.length === 0)">
      <div v-for="(warning,$index) in warnings" :key="$index" class="input__warning">{{$t(warning)}}</div>
    </div>
  </div>
</template>
<script>
export default {
  methods: {
    printError(error) {
      if (Array.isArray(error)) {
        return this.$tc(error[0], error[1]);
      } else {
        return this.$t(error);
      }
    },
  },
  props: {
    warnings: {
      type: Array,
      required: false,
    },
    errors: {
      type: Array,
      required: false
    },
  }
}
</script>
