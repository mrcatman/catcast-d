<template>
  <c-box>
    <template slot="title">
      {{$t('settings.password.heading')}}
    </template>
    <template slot="main">
      <!-- todo: title, change email, 2fa, delete account -->
      <c-form box url="/auth/password">
        <c-input v-form-input="'old_password'"  type="password" :title="$t('settings.password.old_password')"  />
        <c-input v-form-input="'new_password'" type="password" :title="$t('settings.password.new_password')" />
        <c-input v-form-input="'new_password_confirmation'" type="password" :title="$t('settings.password.password_confirmation')" />
      </c-form>
    </template>
  </c-box>
</template>
<style lang="scss">

</style>
<script>
export default {

}
</script>
