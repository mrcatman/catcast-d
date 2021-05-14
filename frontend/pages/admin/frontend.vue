<template>
<div v-if="form.frontend">
  <h3 class="controls-page__subheading">{{$t('admin.config.frontend.info')}}</h3>
  <m-input :warnings="warnings['frontend.site_name']" :errors="errors['frontend.site_name']" v-model="form.frontend.site_name" :title="$t('admin.config.frontend.site_name')" />
  <m-editor :warnings="warnings['frontend.site_description']" :errors="errors['frontend.site_description']" v-model="form.frontend.site_description" :title="$t('admin.config.frontend.site_description')" />
  <m-input :warnings="warnings['frontend.site_contact_email']" :errors="errors['frontend.site_contact_email']" v-model="form.frontend.site_contact_email" :title="$t('admin.config.frontend.site_contact_email')" />

  <h3 class="controls-page__subheading">{{$t('admin.config.frontend.design')}}</h3>
  <m-picture-uploader :returnPath="true" v-model="form.frontend.logo" :title="$t('admin.config.frontend.logo')" default="/assets/pictures/site-logo.svg" />
  <m-picture-uploader :returnPath="true" v-model="form.frontend.channel_default_logo" :title="$t('admin.config.frontend.channel_default_logo')" default="/assets/pictures/default-logo.svg" />
  <m-picture-uploader :returnPath="true" v-model="form.frontend.user_default_avatar" :title="$t('admin.config.frontend.user_default_avatar')" default="/assets/pictures/default-avatar.svg" />
  <m-button @click="sendForm" :loading="formIsSubmitting">{{$t('forms.save')}}</m-button>
</div>
</template>
<script lang="ts">
import { Component } from 'nuxt-property-decorator'
import { SiteGetFullConfig, SiteSetConfig } from '~/api/modules/site'

import { BaseFormComponent, Warning } from '~/components/types/BaseFormComponent'
import { notifySuccess } from '~/helpers/notifications'

@Component
export default class AdminSettingsFrontend extends BaseFormComponent {

  fields: any = [
    'frontend.site_name','frontend.site_description', 'frontend.site_contact_email', 'frontend.logo',  'frontend.channel_default_logo', 'frontend.user_default_avatar',
  ];

  async fetch() {
    this.form = await SiteGetFullConfig();
  }

  onSubmit(status: boolean) {
    if (status) {
      notifySuccess(this.$t('common.saved').toString());
    }
  }

  submit() {
    return SiteSetConfig(this.form);
  }

  validateField(field: string, value: string | null) : Array<Warning> | null  {
    if (['frontend.site_name'].includes(field)) {
      if (!value || value.length === 0) {
        return [Warning.FIELD_REQUIRED]
      }
    }
    return null;
  }


}
</script>
