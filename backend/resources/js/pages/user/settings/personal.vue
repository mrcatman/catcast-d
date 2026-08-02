<template>

  <c-box>
    <template #title>{{ $t('profile.personal.heading') }}</template>
    <template #main>
      <c-form-v2
          :initialValues="user"
          :handler="updateUser"
          @success="updateSuccess"
      >
        <template #default="{ values, errors }">
          <c-row>
            <c-col>
              <c-input maxlength="50" v-model="values.username" :errors="errors.username" :title="$t('profile.personal.username')" :append="`@${siteDomain}`" readonly />
            </c-col>
            <c-col>
              <c-input v-model="values.full_name" :errors="errors.full_name" :title="$t('profile.personal.full_name')"/>
            </c-col>
          </c-row>

          <c-picture-uploader big v-model="values.pictures_data.avatar" :errors="errors['pictures_data.avatar']" :title="$t('profile.avatar')" folder="avatars"/>
          <c-text-editor v-model="values.about" :errors="errors.about" :title="$t('profile.personal.description')"/>

          <c-list-input
              v-model="values.links"
              :errors="errors.links"
              :title="$t('profile.personal.links')"
              :fields="[{id: 'title', name: $t('links_editor.heading'), flexGrow: .5}, {id: 'url', name: $t('links_editor.url')}]"
          />
        </template>
      </c-form-v2>
    </template>
  </c-box>

</template>
<script lang="ts" setup>
const { t } = useI18n();

const { request } = useApi();
const { user } = useAuthStore();

const { siteDomain } = useConfigStore();
const { newAlert } = useAlertsStore();

const updateUser = (user: Partial<Auth.User>) => request.put('/auth/me', {body: user});
const {setUser} = useAuthStore();

const updateSuccess = (user) => {
  setUser(user);
  newAlert({
    type: 'success',
    text: t('global.saved'),
  })
}

</script>
