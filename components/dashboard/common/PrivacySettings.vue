<template>
  <c-box>
    <template slot="title">
      {{$t('dashboard.common.privacy._title')}}
    </template>
    <template slot="main">
      <c-checkbox switch :title="$t('dashboard.common.privacy.comments_enabled')" v-form-input="'additional_settings.privacy.comments_enabled'" />
      <c-checkbox switch :title="$t('dashboard.common.privacy.comments_display')" v-form-input="'additional_settings.privacy.comments_display'" v-form-show="'!additional_settings.privacy.comments_enabled'" />
      <c-checkbox switch v-if="canDisableRating" :title="$t('dashboard.common.privacy.rating_enabled')" v-form-input="'additional_settings.privacy.rating_enabled'" />
      <c-checkbox switch :title="$t('dashboard.common.privacy.has_nsfw_content')" v-form-input="'additional_settings.privacy.has_nsfw_content'" />
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
