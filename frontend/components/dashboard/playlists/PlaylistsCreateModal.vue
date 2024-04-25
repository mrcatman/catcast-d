<template>
  <div>
    <c-input v-form-input="'name'" v-form-validate="'required'" :title="$t('dashboard.playlists.name')" />
    <privacy-status-select v-form-input="'privacy_status'" />
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
