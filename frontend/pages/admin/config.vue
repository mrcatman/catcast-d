<template>
<div >
  <m-tabs :list="tabs" v-model="currentTab" />
  <div class="box__content">
    <transition-group name="fade">
      <div key="frontend" v-if="form.frontend" v-show="currentTab === 'frontend'">
        <m-input :warnings="warnings['frontend.site_name']" :errors="errors['frontend.site_name']" v-model="form.frontend.site_name" :title="$t('admin.config.frontend.site_name')" />
        <m-editor :warnings="warnings['frontend.site_description']" :errors="errors['frontend.site_description']" v-model="form.frontend.site_description" :title="$t('admin.config.frontend.site_description')" />
        <m-input :warnings="warnings['frontend.site_contact_email']" :errors="errors['frontend.site_contact_email']" v-model="form.frontend.site_contact_email" :title="$t('admin.config.frontend.site_contact_email')" />
        <m-picture-uploader :returnPath="true" v-model="form.frontend.logo" :title="$t('admin.config.frontend.logo')" default="/assets/pictures/site-logo.svg" />
        <m-picture-uploader :returnPath="true" v-model="form.frontend.channel_default_logo" :title="$t('admin.config.frontend.channel_default_logo')" default="/assets/pictures/default-logo.svg" />
        <m-picture-uploader :returnPath="true" v-model="form.frontend.user_default_avatar" :title="$t('admin.config.frontend.user_default_avatar')" default="/assets/pictures/default-avatar.svg" />
      </div>
      <div key="federation" v-if="form.federation" v-show="currentTab === 'federation'">
        <m-checkbox v-model="form.federation.enabled" :title="$t('admin.config.federation.enabled')" />
        <m-radio-buttons :inline="true" :values="federationRestrictionTypes" v-model="federationRestrictionType" :title="$t('admin.config.federation.restriction._title')" />
        <m-editable-items-list :warnings="warnings['federation.accept_from']" :errors="errors['federation.accept_from']" v-if="federationRestrictionType === 'whitelist'" v-model="form.federation.accept_from" />
        <m-editable-items-list :warnings="warnings['federation.reject_from']" :errors="errors['federation.reject_from']" v-else-if="federationRestrictionType === 'blacklist'" v-model="form.federation.reject_from" />
      </div>
    </transition-group>
    <m-button @click="sendForm" :loading="formIsSubmitting">{{$t('forms.save')}}</m-button>
  </div>

</div>
</template>
<script lang="ts">
import { Component, Vue } from 'nuxt-property-decorator'
import { SiteGetFullConfig, SiteSetConfig } from '~/api/modules/site'
import TabData from '~/components/types/Tabs'
import { BaseFormComponent, Warning } from '~/components/types/BaseFormComponent'
import { notifySuccess } from '~/helpers/notifications'
import { Watch } from 'vue-property-decorator'

@Component
export default class AdminConfigPage extends BaseFormComponent {
  currentTab = 'frontend';
  federationRestrictionType = 'none';
  federationRestrictionTypes =  [
    {id: 'none', title: this.$t('admin.config.federation.restriction.none')},
    {id: 'whitelist', title: this.$t('admin.config.federation.restriction.whitelist')},
    {id: 'blacklist', title: this.$t('admin.config.federation.restriction.blacklist')},
  ];
  @Watch('federationRestrictionType')
  clearRestrictionList(restrictionType: string) {
   if (restrictionType === 'whitelist') {
     this.form.federation.reject_from = [];
   }
    if (restrictionType === 'blacklist') {
      this.form.federation.accept_from = [];
    }
  }

  fields: any = [
    'frontend.site_name','frontend.site_description', 'frontend.site_contact_email', 'frontend.logo',  'frontend.channel_default_logo', 'frontend.user_default_avatar',
  ];
  tabs = [
    {id: 'frontend', name: this.$t('admin.config.tabs.frontend') as string},
    {id: 'federation', name: this.$t('admin.config.tabs.federation') as string},
  ] as Array<TabData>;

  async fetch() {
    this.form = await SiteGetFullConfig();
    this.federationRestrictionType = (this.form.federation.reject_from.length ? 'blacklist' : (this.form.federation.accept_from.length ? 'whitelist' : 'none'));
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
