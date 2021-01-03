<template>
  <div>
    <m-input :warnings="warnings.name" :errors="errors.name" v-model="form.name" :title="$t('channels.forms.name')" />
    <m-input prepend="https://catcast.tv/" :warnings="warnings.url" :errors="errors.url" v-model="form.url" :title="$t('channels.forms.url')" />
    <m-input :warnings="warnings.description" :errors="errors.description" type="textarea" v-model="form.description" :title="$t('channels.forms.description')" />

    <m-picture-uploader v-model="form.logo" :title="$t('channels.forms.logo')" />

    <m-button @click="sendForm" :loading="formIsSubmitting">{{$t('forms.save')}}</m-button>
  </div>

</template>

<script lang="ts">
  import { Component, Prop } from 'vue-property-decorator'
  import {BaseFormComponent, Warning} from '~/components/types/BaseFormComponent'
  import Channel from '~/types/Channel'
  import { ChannelCreate, ChannelUpdate } from '~/api/modules/channels'
  import { notifySuccess } from '~/helpers/notifications'
  import { Route } from "vue-router"

  @Component
  export default class ChannelCommon extends BaseFormComponent {

    @Prop({required: false})
    public channel!: Channel;

    fields: any = ['name', 'url', 'description'];
    form = {} as Channel;

    mounted() {
      if (this.channel) {
        this.form = this.channel;
      }
    }

    onSubmit(channel: Channel) {
      notifySuccess(this.$t('global.saved').toString());
      if (!this.channel) {
        this.$router.push(`/dashboard/${channel.id}/main`);
      }
    }
    submit() {
      return this.channel ? ChannelUpdate(this.form) : ChannelCreate(this.form);
    }
    validate(field: string, value: string) : Array<Warning> | null {
      if (field === 'name' || field === 'url') {
        if (!value || value.length === 0) {
          return [Warning.FIELD_REQUIRED]
        }
      }
      return null;
    }
  }
</script>
