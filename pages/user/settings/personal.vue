<template>
  <div class="personal-settings">
     <c-box>
       <template slot="title">{{$t('profile.personal.heading')}}</template>
       <template slot="main">
         <c-form box :initialValues="user" @response="onResponse" method="put" url="/auth/me">
           <c-row>
             <c-col>
               <c-input v-form-input="'username'" :title="$t('profile.personal.username')" :append="`@${siteDomain}`" :readonly="true" />
             </c-col>
             <c-col>
               <c-input v-form-input="'full_name'" :title="$t('profile.personal.full_name')"  />
             </c-col>
           </c-row>

           <c-picture-uploader big v-form-input="'pictures_data.avatar'"  :title="$t('profile.avatar')" folder="avatars" />
           <c-text-editor v-form-input="'about'" :title="$t('profile.personal.description')" />

           <c-list-input v-form-input="'links'" :fields="[{id: 'title', name: $t('links_editor.title'), flexGrow: .5}, {id: 'url', name: $t('links_editor.url')}]" :title="$t('profile.personal.links')" />
         </c-form>
       </template>
     </c-box>
  </div>
</template>
<style lang="scss">
  .personal-settings {

  }
</style>
<script>
import {mapGetters, mapState} from "vuex";

  export default {
    computed: {
      ...mapGetters('config', ['siteDomain']),
      ...mapState('auth', ['user']),
    },

    methods: {
      onResponse(user) {
        if (!user._has_errors) {
          this.$store.commit('auth/setUser', user);
        }
      },
    }
  }
</script>
