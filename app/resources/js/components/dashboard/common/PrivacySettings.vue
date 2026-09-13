<template>
  <c-box>
    <template #title>
      {{$t('dashboard.common.privacy.heading')}}
    </template>
    <template #main>
      <c-checkbox switch :title="$t('dashboard.common.privacy.comments_enabled')" v-model="values.additional_settings.privacy.comments_enabled" :errors="errors.additional_settings.privacy.comments_enabled" />
      <c-checkbox switch :title="$t('dashboard.common.privacy.comments_display')" v-model="values.additional_settings.privacy.comments_display" :errors="errors.additional_settings.privacy.comments_display" v-form-show="'!additional_settings.privacy.comments_enabled'" />
      <c-checkbox switch v-if="canDisableRating" :title="$t('dashboard.common.privacy.rating_enabled')" v-model="values.additional_settings.privacy.rating_enabled" :errors="errors.additional_settings.privacy.rating_enabled" />
      <c-checkbox switch :title="$t('dashboard.common.privacy.has_nsfw_content')" v-model="values.additional_settings.privacy.has_nsfw_content" :errors="errors.additional_settings.privacy.has_nsfw_content" />
    </template>
  </c-box>
</template>
<script>
export default {
  props: {
    canDisableRating: Boolean
  }
}
</script>
