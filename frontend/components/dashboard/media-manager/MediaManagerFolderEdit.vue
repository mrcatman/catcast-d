<template>
  <div>
    <c-input v-model="values.title" :errors="errors.title" v-form-validate="'required'" :title="$t('dashboard.media.folders.title')" />
    <c-checkbox :title="$t('dashboard.media.folders.is_private')" v-model="values.is_private" :errors="errors.is_private" />
  </div>
</template>
<script>
export default {

}
</script>
