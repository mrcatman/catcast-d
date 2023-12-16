<template>
  <div class="centered-block">
    <div class="box">
      <div class="row">
        <div class="after-register-form">
          <div v-if="loading" class="after-register-form__preloader">
            <c-preloader  />
          </div>
          <div v-else>
            <c-response :data="response" />
            <div v-if="currentStep === 0">
              <div class="after-register-form__text">
                {{$t('auth.after_register.text_username')}}
              </div>
              <c-input v-model="form.username" :title="$t('auth.username')" :warnings="warnings.username" :errors="errors.username" />
              <div class="after-register-form__buttons">
                <div class="buttons-row">
                  <c-button @click="saveUsername"  :loading="loading.username" :disabled="form.username.length == 0">{{$t('global.next')}}</c-button>
                  <c-button flat @click="currentStep = 1">{{$t('global.skip')}}</c-button>
                </div>
              </div>
            </div>
            <div v-else-if="currentStep === 1">
              <div class="after-register-form__text">
                {{$t('auth.after_register.text_password')}}
              </div>
              <c-input type="password" v-model="form.password" :title="$t('auth.password')" :warnings="warnings.password" :errors="errors.password" />
              <c-input type="password" v-model="form.password_confirmation" :title="$t('auth.password_confirmation')" :warnings="warnings.password_confirmation" :errors="errors.password_confirmation" />
              <div class="after-register-form__buttons">
                <div class="buttons-row">
                  <c-button :disabled="!formCorrect" :loading="loading.password" @click="savePassword" >{{$t('global.next')}}</c-button>
                  <c-button flat @click="currentStep = 2">{{$t('global.skip')}}</c-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
  .after-register-form{
    max-width: 560px;
    &__text {
      padding: 0 0 .5em;
    }
    &__buttons {
      margin: .5em 0 0;
    }
    &__preloader{
      text-align: center;
    }
  }
</style>
<script>
  export default {
    head() {
      return {
        title: this.$t('auth.after_register._title')
      }
    },
    computed: {
      formCorrect() {
        let count = 0;
        Object.keys(this.warnings).forEach(key=>{
          count += this.warnings[key].length;
        });
        return count === 0;
      }
    },
    watch: {
      currentStep(step) {
        if (step === 2) {
          const userData = this.$store.state.userData;
          this.$router.push('/users/'+userData.id);
        }
      },
      "form.password_confirmation":function(newPassword) {
        this.warningHandler("password_confirmation");
      },
      "form.password":function(newPassword) {
        this.warningHandler("password");
      },
      "form.username":function(newUsername) {
        this.warningHandler("username");
      }
    },
    mounted() {
      const token = this.$route.query.token;
      console.log('token', token);
      if (token) {
        this.$store.commit('SET_TOKENS', {token});
        this.$store.dispatch('setUserData',{token: token}).then(() => {
          localStorage.setItem('auth_event', Math.random());
          const userData = this.$store.state.userData;
          if (!this.$route.query.is_new) {
            this.$router.push('/users/'+userData.id);
          } else {
            this.form.username = userData.username;
            this.loading = false;
          }
        });
      } else {
        this.$router.push('/');
      }
    },
    data() {
      return {
        loading: true,
        currentStep: 0,
        form: {
            username: '',
            password: '',
            password_confirmation: '',
        },
        warnings: {},
        errors: {},
        loading: {
          username: false,
          password: false,
        },
        response:null,
      }
    },
    methods: {
      warningHandler(key) {
        this.errors = {};
        const val = this.form[key];
        if (key === "username") {
          if (val.length === 0) {
            this.$set(this.warnings, 'username', ['errors.field_required']);
          } else {
            this.$set(this.warnings, 'username', []);
          }
        }
        if (key === "password") {
          if (val.length === 0) {
            this.$set(this.warnings,'password',['errors.field_required']);
          } else {
            if (val.length < 7) {
              this.$set(this.warnings,'password',['auth.user._errors.password_short']);
            } else {
              if (!/^[a-z0-9]+$/i.test(val)) {
                this.$set(this.warnings,'password',['auth.user._errors.password_alphanumeric']);
              } else {
                this.$set(this.warnings,'password',[]);
              }
            }
          }
        }
        if (key === "password_confirmation") {
          if (val !== this.form.password) {
            this.$set(this.warnings,'password_confirmation',['auth.user._errors.password_match']);
          } else {
            this.$set(this.warnings,'password_confirmation',[]);
          }
        }
      },
      savePassword() {
        ["password","password_confirmation"].forEach(key=>this.warningHandler(key));
        if (this.formCorrect) {
          this.updateUserData({password: this.form.password});
        }
      },
      saveUsername() {
        this.updateUserData({username:this.form.username});
      },
      updateUserData(data){
        this.$axios.post('auth/me',data).then(res=>{
          this.errors = res.data.errors || {};
          if (res.data.status) {
            this.currentStep++;
            if (data.username) {
              let userData = this.$store.state.userData;
              userData.username = data.username;
              this.$store.commit('auth/setUser', userData);
            }
          } else {
            this.response = res.data;
          }
        })
      },
    }
  }
</script>
