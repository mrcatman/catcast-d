<template>
  <div>
    <m-input :readonly="!!profile" :append="`@${domain}`" :warnings="warnings.login" :errors="errors.login" v-model="form.login" :title="$t('auth.forms.login')" />

    <m-input :warnings="warnings.name" :errors="errors.name" v-model="form.name" :title="$t('auth.forms.name')" />
    <m-input :warnings="warnings.about" :errors="errors.about" type="textarea" v-model="form.about" :title="$t('auth.forms.about')" />
    <m-picture-uploader v-model="form.avatar" :title="$t('auth.forms.avatar')" />

    <m-button @click="sendForm" :loading="formIsSubmitting">{{$t('forms.save')}}</m-button>
  </div>

</template>
<script lang="ts">
  import { Component, Prop } from 'vue-property-decorator'
  import {BaseFormComponent, Warning} from '~/components/types/BaseFormComponent'
  import User from '~/types/User'
  import { AuthUpdateProfile } from '~/api/modules/auth'
  import { Route } from "vue-router"

  @Component
  export default class EditProfile extends BaseFormComponent {
    @Prop({default: null}) readonly profile!: User | null
    fields: any = ['name', 'about'];
    form = {} as User;

    get domain() {
      return this.$accessor.modules.site?.config?.domain;
    }

    mounted() {
      if (this.profile) {
        this.form = JSON.parse(JSON.stringify(this.profile));
      }
    }
    onSubmit(user: User) {
      this.$accessor.modules.auth.setUser(user);
      this.$router.push('/');
    }
    submit() {
       return AuthUpdateProfile(this.form);
    }
    validateField(field: string, value: string) : Array<Warning> | null {
      return null;
    }
  }
</script>
