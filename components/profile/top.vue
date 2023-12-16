<template>
  <c-box class="profile-page__top__container">
    <template slot="main">
      <div class="profile-page__top">
        <div class="profile-page__top__info">
          <div :style="{backgroundImage: `url(${user.avatar})`}"  class="profile-page__avatar"></div>
          <div class="profile-page__user">
            <span class="profile-page__user__text">{{$t('profile.user')}} </span>
            <router-link :to="`/users/${user.id}`" class="profile-page__user__name">
               {{username}}
               <c-tag v-if="me?.id === user.id">{{$t('profile.you')}}</c-tag>
               <c-tag color="green" v-if="user.is_admin">{{$t('profile.admin')}}</c-tag>
            </router-link>
            <user-online-mark :user="user"/>
          </div>
        </div>
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
    }
  }
  &__user {
    display: flex;
    flex-direction: column;
    margin-left: 1em;
    &__text {
      margin-bottom: 0.25em;
      font-size: .875em;
      line-height: 1;
    }
    &__name {
      text-decoration: none;
      font-weight: 600;
      font-size: 1.25em;
    }
  }
  &__status {
    margin: 0 1em;
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
<script>
import UserOnlineMark from "@/components/users/UserOnlineMark";
import FriendsButton from "@/components/buttons/FriendsButton";
export default {
  components: {FriendsButton, UserOnlineMark},
  computed: {
    username() {
      return `${this.user.username}${this.user.domain ? `@${this.user.domain}` : ''}`;
    },
    me() {
      return this.$store.state.user;
    },
  },
  data() {
    return {
      statusText: this.user.status_text && this.user.status_text !== '' ? this.user.status_text : null,
      editStatus: {
        loading: false,
        visible: false,
        data: {
          status_text: this.user.status_text || '',
        }
      }
    }
  },
  methods: {
    saveStatus() {
      this.editStatus.loading = true;
      this.$api.put('/auth/me', this.editStatus.data).then(({status_text})=> {
        this.editStatus.data.status_text = status_text;
        this.statusText = status_text;
        this.editStatus.visible = false;
      }).finally(() => {
        this.editStatus.loading = false;
      })
    },
  },
  props: {
    user: {
      type: Object,
      required: true
    },
    accessSettings: {
      type: Object,
      required: true
    }
  }
}
</script>
