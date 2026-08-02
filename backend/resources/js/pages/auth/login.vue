<template>
  <auth-form>
    <template #main>
      <c-form-v2
          :handler="login"
          @success="loginSuccess"
          :submit-button="{
          text: $t('auth.login_action'),
          props: {
            big: true
          }
        }"
      >
        <template #default="{ values, errors }">
          <c-input v-model="values.username" :errors="errors.username" :title="$t('auth.username')"/>
          <c-input v-model="values.password" :errors="errors.password" type="password" :title="$t('auth.password')"/>
        </template>
      </c-form-v2>
    </template>
    <template #links>
      <router-link to="/auth/forgot-password">{{ $t('auth.forgot_password.link') }}</router-link>
      <router-link to="/auth/register">{{ $t('auth.register') }}</router-link> <!-- v-if="registrationEnabled" -->
      <a @click="login">test</a>
    </template>
  </auth-form>
</template>
<script lang="ts" setup>
const {request} = useApi();
const route = useRoute();
const router = useRouter();
const {t} = useI18n();

const login = (credentials: Auth.Credentials) => request.post('/auth/login', {body: credentials});
const {setUser} = useAuthStore();

definePageMeta({
  middleware: [
    'not-auth',
  ]
});

useHead({
  title: t('auth.login')
});

const loginSuccess = (user: Auth.User) => {
  setUser(user);

  if (route.query && route.query.return) {
    router.push(route.query.return);
  } else {
    router.push(`/users/${user.id}`);
  }
}

//
// export default {
//   middleware: 'not-auth',
//   computed: {
//     ...mapState('auth', ['loggedIn']),
//     ...mapGetters('config', ['registrationEnabled'])
//   },
//
// 	methods: {
//     onStorage(e) {
//       if (e.key === "auth_event") {
//         window.close();
//       }
//     },
// 		async
// 	}
// }
</script>
