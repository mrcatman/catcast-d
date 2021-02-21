<template>
   <m-modal :title="$t('streams.info')" v-model="visible">
     <m-input :warnings="warnings.name" :errors="errors.name" v-model="form.name" :title="$t('streams.name')" />
     <m-input :warnings="warnings.description" :errors="errors.description" type="textarea" v-model="form.description" :title="$t('streams.description')" />

     <m-button @click="sendForm" :loading="formIsSubmitting">{{$t('forms.save')}}</m-button>
   </m-modal>
</template>
<script lang="ts">
  import Component from 'vue-class-component';
  import { BaseFormComponent, Warning } from '~/components/types/BaseFormComponent'
  import { ChannelSetStreamSettings } from '~/api/modules/channels'
  import { Prop, Watch } from 'vue-property-decorator'
  import Channel from '~/types/Channel'
  import { notifySuccess } from '~/helpers/notifications'

  @Component()
  export default class StreamInfoModal extends BaseFormComponent {
    @Prop({required: true})
    public channel!: Channel;

    @Prop({required: true})
    public value!: boolean;

    public visible: boolean = false;

    @Watch('value')
    onValueChange(value) {
      this.visible = value;
    }
    @Watch('visible')
    onVisibleChange(value) {
      this.$emit('input', value);
    }

    fields: any = ['name', 'description'];
    form = {
      name: this.channel.stream_settings.name || '',
      description: this.channel.stream_settings.description || ''
    }
    onSubmit() {
      notifySuccess(this.$t('common.saved').toString());
      this.visible = false;
    }
    submit() {
      return ChannelSetStreamSettings(this.channel.id, this.form);
    }
    validateField(field: string, value: string) : Array<Warning> | null {
      if (field === 'name') {
        if (!value || value.length === 0) {
          return [Warning.FIELD_REQUIRED]
        }
      }
      return null;
    }
  }
</script>
