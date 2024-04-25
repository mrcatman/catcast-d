<template>
  <div>
    <c-input v-form-input="'title'" v-form-validate="'required'" :title="$t('dashboard.media.folders.title')" />
    <c-checkbox :title="$t('dashboard.media.folders.is_private')" v-form-input="'is_private'" />
  </div>
</template>
<script>
export default {

}
</script>
