<template>
  <div>
    <m-input :append="`@${domain}`" :warnings="warnings.login" :errors="errors.login" v-model="form.login" :title="$t('auth.forms.login')" />
    <m-input :warnings="warnings.password" :errors="errors.password" type="password" v-model="form.password" :title="$t('auth.forms.password')" />

    <m-button @click="sendForm" :loading="formIsSubmitting" big>{{$t('auth.forms.login_button')}}</m-button>
  </div>

</template>
<script lang="ts">
  import { Component, } from 'vue-property-decorator'
  import {BaseFormComponent, Warning} from '~/components/types/BaseFormComponent'
  import { AuthLogin } from '@/api/modules/auth'
  import User from '~/types/User'

  @Component
  export default class Login extends BaseFormComponent {
    get domain() {
      return this.$accessor.modules.site?.config?.domain;
    }
    fields: any = ['login', 'password'];
    form = {
      login: '',
      password: ''
    }
    onSubmit(user: User) {
      this.$emit('auth', user);
    }
    submit() {
      return AuthLogin(this.form.login, this.form.password);
    }
    validateField(field: string, value: string) : Array<Warning> | null {
      if (!value || value.length === 0) {
        return [Warning.FIELD_REQUIRED]
      }
      return null;
    }
  }
</script>
