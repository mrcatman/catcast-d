<template>
  <c-box>
    <template #title>
      {{$t('settings.password.heading')}}
    </template>
    <template #main>
      <!-- todo: title, change email, 2fa, delete account -->
      <c-form-v2 :handler="updatePassword">
        <template #default="{ values, errors }">
          <c-input v-model="values.old_password" :errors="errors.old_password"  type="password" :title="$t('settings.password.old_password')"  />
          <c-input v-model="values.new_password" :errors="errors.new_password" type="password" :title="$t('settings.password.new_password')" />
          <c-input v-model="values.new_password_confirmation" :errors="errors.new_password_confirmation" type="password" :title="$t('settings.password.password_confirmation')" />
        </template>
      </c-form-v2>
    </template>
  </c-box>
</template>
<script lang="ts" setup>
const { request } = useApi();
const updatePassword = (updatePasswordBody: Auth.UpdatePassword) => request.post('/auth/password', {body: updatePasswordBody});
</script>
