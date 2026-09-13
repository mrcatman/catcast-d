<template>
  <div>
    <c-input v-model="values.title" :errors="errors.title" v-form-validate="'required'" :title="$t('dashboard.broadcast.metadata.title')" />
    <c-text-editor v-model="values.description" :errors="errors.description" :title="$t('dashboard.broadcast.metadata.description')" />
    <c-autocomplete v-model="values.category" :errors="errors.category" autocomplete-key="id" autocomplete-value="name" url="categories" :title="$t('dashboard.broadcast.metadata.category')"/>
    <c-tags-input v-model="values.tags" :errors="errors.tags" :title="$t('dashboard.broadcast.metadata.tags')"/>
    <div v-if="planned">
      <c-row>
        <c-col>
          <c-datetime-picker minDate="today" :title="$t('dashboard.broadcast.metadata.will_start_at')"  v-model="values.will_start_at" :errors="errors.will_start_at" />
        </c-col>
        <c-col>
          <c-datetime-picker minDate="today" :title="$t('dashboard.broadcast.metadata.will_end_at')"  v-model="values.will_end_at" :errors="errors.will_end_at" />
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
