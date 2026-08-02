<template>
  <c-box class="profile-page__top__container">
    <template #main>
      <div class="profile-page__top">
        <div class="profile-page__top__info">
          <div :style="{backgroundImage: `url(${user.avatar})`}"  class="profile-page__avatar"></div>
          <div class="profile-page__user">
            <span class="profile-page__user__text">{{$t('profile.user')}} </span>
            <router-link :to="`/users/${user.id}`" class="profile-page__user__name">
               {{username}}
               <c-tag v-if="me?.id === user.id">{{$t('profile.you')}}</c-tag>
               <c-tag color="green" v-if="user.is_admin">{{$t('profile.admin')}}</c-tag>

              <div class="profile-page__status">
                <span class="profile-page__status__text profile-page__status__text--empty" v-if="!statusText && me?.id === user.id && !editStatus.visible">{{$t('profile.change_status')}}</span>
                <span class="profile-page__status__text" v-if="statusText && !editStatus.visible">{{statusText}}</span>
                <c-button transparent icon-only icon="edit" v-if="me?.id === user.id && !editStatus.visible" @click="editStatus.visible = true" />
                <c-row v-if="editStatus.visible">
                  <c-col>
                    <c-input v-model="editStatus.data.status_text"></c-input>
                  </c-col>
                  <c-col auto-width>
                    <c-button color="green" :loading="editStatus.loading" @click="saveStatus()">{{$t('global.ok')}}</c-button>
                  </c-col>
                </c-row>
              </div>
            </router-link>
            <user-online-status :user="user"/>
          </div>
        </div>

      </div>
    </template>
  </c-box>
</template>
<style lang="scss">
.profile-page {
  &__top {
    display: flex;
    align-items: center;
    justify-content: flex-start;

    &__container {
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    &__info {
      display: flex;
      align-items: center;
      gap: 1em;
    }
  }
  &__user {
    display: flex;
    flex-direction: column;
    gap: .5em;
    &__text {
      font-size: .875em;
      line-height: 1;
    }
    &__name {
      text-decoration: none;
      font-weight: 600;
      font-size: 1.25em;
      display: flex;
      align-items: center;
      gap: .5em;
    }
    .tag {
      font-size: .75em;
      margin: 0;
    }
  }
  &__status {
    flex: 1;
    display: flex;
    align-items: center;
    &__text {
      max-width: 50%;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 4;
      overflow: hidden;
      margin-right: .5em;
      &--empty {
        font-style: italic;
      }
    }
  }
  &__avatar {
    width: 3.5em;
    height: 3.5em;
    background-size: contain!important;
  }
}
</style>
<script lang="ts" setup>
import UserOnlineStatus from "../users/UserOnlineStatus.vue";

const props = defineProps<{
  user: Users.Item,
  accessSettings: any,
}>();

const { user: me } = useAuthStore();

const username = computed(() => {
  return `${props.user.username}${props.user.domain ? `@${props.user.domain}` : ''}`;
})

const statusText = ref<string>(props.user.status_text ?? null);

const editStatus = {
  visible: false,
  loading: false
}
const saveStatus = (() => {
  // this.editStatus.loading = true;
  // this.$api.put('/auth/me', this.editStatus.data).then(({status_text})=> {
  //   this.editStatus.data.status_text = status_text;
  //   this.statusText = status_text;
  //   this.editStatus.visible = false;
  // }).finally(() => {
  //   this.editStatus.loading = false;
  // })
})

</script>
