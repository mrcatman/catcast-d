<template>
  <c-select v-model="val" :options="privacyStatusOptions" :title="$t('privacy_statuses._title')"/>

</template>
<script>
export default {
  watch: {
    val(newVal) {
      this.$emit('input', newVal);
    }
  },
  data() {
    return {
      val: 0,
      privacyStatusOptions: [
        {name: this.$t('privacy_statuses.public'), value: 2},
        {name: this.$t('privacy_statuses.unlisted'), value: 1},
        {name: this.$t('privacy_statuses.private'), value: 0},
      ]
    }
  }
}
</script>
