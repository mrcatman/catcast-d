<template>
  <div>
    <m-input append="@catcast.tv" :warnings="warnings.login" :errors="errors.login" v-model="form.login" :title="$t('auth.forms.login')" />
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

  @Component
  export default class EditProfile extends BaseFormComponent {
    @Prop({default: null}) readonly profile!: User | null
    fields: any = ['about'];
    form = {} as User;
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
    validate(field: string, value: string) : Array<Warning> | null {
      return null;
    }
  }
</script>
