<template>
 <div v-if="form.federation" >
   <m-checkbox v-model="form.federation.enabled" :title="$t('admin.config.federation.enabled')" />
   <div v-show="form.federation.enabled">
     <m-radio-buttons :inline="true" :values="federationRestrictionTypes" v-model="federationRestrictionType" :title="$t('admin.config.federation.restriction._title')" />
     <m-editable-items-list :warnings="warnings['federation.accept_from']" :errors="errors['federation.accept_from']" v-if="federationRestrictionType === 'whitelist'" v-model="form.federation.accept_from" />
     <m-editable-items-list :warnings="warnings['federation.reject_from']" :errors="errors['federation.reject_from']" v-else-if="federationRestrictionType === 'blacklist'" v-model="form.federation.reject_from" />
   </div>
   <m-button @click="sendForm" :loading="formIsSubmitting">{{$t('forms.save')}}</m-button>
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
export default class AdminSettingsFederation extends BaseFormComponent {
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

  fields: any = [];

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
    return null;
  }


}
</script>
