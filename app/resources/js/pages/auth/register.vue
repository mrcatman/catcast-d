<template>
  <auth-form class="register">
    <template #main>
      <div class="register__form">
        <transition name="fade" mode="out-in">
          <div v-if="!acceptedRules">
            <c-long-text :text="instanceRules"/>
            <c-button big @click="acceptedRules = true">{{ $t('global.next') }}</c-button>
          </div>
          <c-form-v2
              v-else-if="!success"
              :handler="register"
              @success="registerSuccess"
              :submit-button="{
                text: registrationManual ? $t('auth.register_action_manual') : $t('auth.register_action'),
                props: {
                  big: true
                }
              }"
          >
            <template #default="{ values, errors }">
              <c-input v-model="values.email" :errors="errors.email" required :title="$t('auth.email')"/>
              <c-input v-model="values.username" :errors="errors.username" required :append="`@${siteDomain}`"
                       :title="$t('auth.username')"/>

              <div class="vertical-delimiter"></div>

              <c-input v-model="values.password" :errors="errors.password" required type="password"
                       :title="$t('auth.password')"/>
              <c-input v-model="values.password_confirmation" :errors="errors.password_confirmation" required
                       type="password" :title="$t('auth.password_confirmation')"/>

              <template v-if="registrationManual">
                <div class="vertical-delimiter"></div>
                <c-input
                    v-model="values.request_comment"
                    :errors="errors.request_comment"
                    required type="textarea"
                    :title="$t('auth.registration_request_comment')"
                    :description="$t('auth.registration_manual')"
                />
              </template>
            </template>
          </c-form-v2>
          <div class="register__success" v-else>
            {{ $t('auth.registration_success') }}
          </div>
        </transition>
      </div>
    </template>
    <template #links>
      <router-link v-if="!success" target="_blank" to="/auth/privacy-policy">{{
          $t('auth.privacy_policy')
        }}
      </router-link>
      <router-link v-if="!success" to="/auth/login">{{ $t('auth.login') }}</router-link>
      <router-link v-if="success" to="/auth/resend-confirmation">{{ $t('auth.resend_confirmation.link') }}</router-link>
    </template>
  </auth-form>

</template>
<style lang="scss" scoped>
.register {
  &__form {
    max-width: 30em;

    ::v-deep .long-text {
      margin-bottom: 1em;
    }
  }

  &__success {
    font-weight: 600;
    font-size: 1.125em;
    line-height: 1.4;
    text-align: center;
    margin: 0 auto;
  }
}
</style>
<script lang="ts" setup>
const {instanceRules, registrationManual} = useConfigStore();

definePageMeta({
  middleware: [
      'not-auth',
    'check-registration-enabled'
  ]
});

const success = ref(false)
const acceptedRules = ref(false)

</script>
