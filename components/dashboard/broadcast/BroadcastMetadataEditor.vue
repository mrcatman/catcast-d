<template>
  <div>
    <c-input v-form-input="'title'" v-form-validate="'required'" :title="$t('dashboard.broadcast.metadata.title')" />
    <c-text-editor v-form-input="'description'" :title="$t('dashboard.broadcast.metadata.description')" />
    <c-autocomplete v-form-input="'category'" autocomplete-key="id" autocomplete-value="name" url="broadcasts/categories" :title="$t('dashboard.broadcast.metadata.category')"/>
    <div v-if="planned">
      <c-row>
        <c-col>
          <c-datetime-picker minDate="today" :title="$t('dashboard.broadcast.metadata.will_start_at')"  v-form-input="'will_start_at'" />
        </c-col>
        <c-col>
          <c-datetime-picker minDate="today" :title="$t('dashboard.broadcast.metadata.will_end_at')"  v-form-input="'will_end_at'" />
        </c-col>
      </c-row>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    planned: Boolean
  }
}
</script>
