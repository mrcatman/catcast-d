<template>
  <div>
    <c-input v-model="values.name" :errors="errors.name" v-form-validate="'required'" :title="$t('dashboard.playlists.name')" />
    <privacy-status-select v-model="values.privacy_status" :errors="errors.privacy_status" />
  </div>
</template>
<script>
import PrivacyStatusSelect from "@/components/dashboard/common/PrivacyStatusSelect";
export default {
  components: {PrivacyStatusSelect},
  props: {
    componentProps: Object,
  }
}
</script>
