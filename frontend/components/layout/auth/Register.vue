<template>
  <div>
    <m-input :description="$t('auth.forms.email_description')" :warnings="warnings.email" :errors="errors.email" v-model="form.email" :title="$t('auth.forms.email')" />
    <m-input append="@catcast.tv" :warnings="warnings.login" :errors="errors.login" v-model="form.login" :title="$t('auth.forms.login')" />
    <m-input :warnings="warnings.password" :errors="errors.password" type="password" v-model="form.password" :title="$t('auth.forms.password')" />
    <m-input :warnings="warnings.repeat_password" :errors="errors.repeat_password" type="password" v-model="form.repeat_password" :title="$t('auth.forms.repeat_password')" />

    <m-button @click="sendForm" :loading="formIsSubmitting" big>{{$t('auth.forms.register_button')}}</m-button>
  </div>

</template>
<script lang="ts">
  import { Component, } from 'vue-property-decorator'
  import {BaseFormComponent, Warning} from '~/components/types/BaseFormComponent'
  import {AuthRegister} from '@/api/modules/auth';
  import User from '~/types/User'

  @Component
  export default class Register extends BaseFormComponent {
    fields: any = ['login', 'username', 'password', 'repeat_password'];
    form = {
      email: '',
      login: '',
      password: ''
    }
    onSubmit(user: User) {
      this.$emit('auth', user);
    }
    submit() {
      return AuthRegister(this.form.email, this.form.login, this.form.password);
    }
    validate(field: string, value: string) : Array<Warning> | null {
      if (!value || value.length === 0) {
        return [Warning.FIELD_REQUIRED]
      }
      return null;
    }
  }
</script>
